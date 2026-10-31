<?php

use App\Models\Court;
use App\Models\PriceRule;
use App\Models\Venue;

/*
| Dates used here: Monday 2 Nov 2026, Friday 6 Nov, Saturday 7 Nov.
| The server runs in UTC, Malaysia is 8 hours ahead: 02:00 UTC = 10:00 in Malaysia.
*/

/**
 * An approved venue open Monday 08:00–23:00 and Friday 08:00–12:30 + 14:30–02:00 (a prayer
 * break and a late night), with a badminton court at RM 20.00 an hour and a futsal pitch.
 */
function availabilityVenue(array $settings = []): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'arena-test', ...$settings]);
    $venue->operatingHours()->createMany([
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '08:00', 'closes_at' => '12:30', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '14:30', 'closes_at' => '02:00', 'closes_next_day' => true],
        ['weekday' => 6, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 0, 'opens_at' => '08:00', 'closes_at' => '00:00', 'closes_next_day' => true],
    ]);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    Court::factory()->for($venue)->offering('futsal')->create(['name' => 'Pitch', 'base_price_sen' => 8000]);

    return $venue;
}

/** The start times ("10:00", ...) of the first court in an availability answer. */
function cellTimes($response, int $court = 0): array
{
    return array_column($response->json("data.courts.{$court}.cells"), 'time');
}

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00'); // Monday 10:00 in Malaysia
});

it('FR-11 lists the half hours players can still book today, with prices', function () {
    $venue = availabilityVenue();
    Court::factory()->for($venue)->offering('badminton')->unpriced()->create(['name' => 'No price']);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Repairs', 'is_active' => false]);

    $response = $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-02&sport=badminton')
        ->assertOk()
        ->assertJsonPath('data.closed_reason', null)
        ->assertJsonCount(1, 'data.courts') // only the priced, active badminton court
        ->assertJsonPath('data.courts.0.name', 'Court 1')
        ->assertJsonPath('data.courts.0.cells.0.starts_at', '2026-11-02T10:00:00+08:00')
        ->assertJsonPath('data.courts.0.cells.0.price_sen', 1000) // half of RM 20.00
        ->assertJsonPath('data.courts.0.cells.0.available', true);

    // 10:00 (now) to 22:30, the last half hour before 23:00. Earlier ones have passed.
    expect(cellTimes($response))->toHaveCount(26)
        ->and(cellTimes($response)[25])->toBe('22:30');
});

it('shows every bookable court when no sport is chosen', function () {
    availabilityVenue();

    $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-02')
        ->assertOk()
        ->assertJsonCount(2, 'data.courts');
});

it('FR-13 lets players book right up to the start time, or with the venue\'s notice', function () {
    $this->travelTo('2026-11-02 11:50:00'); // Monday 7:50 pm

    availabilityVenue();
    $response = $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-02&sport=badminton');
    expect(cellTimes($response)[0])->toBe('20:00'); // book at 7:50 pm for 8:00 pm

    // Saved like the Settings tab does, so the kept time grid is thrown away (ADR-028).
    Venue::where('slug', 'arena-test')->firstOrFail()->update(['min_notice_minutes' => 60]);
    $response = $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-02&sport=badminton');
    expect(cellTimes($response)[0])->toBe('21:00'); // 8:00 pm and 8:30 pm are less than an hour away
});

it('ADR-015 lists Friday\'s hours after midnight under Friday, at Friday\'s prices', function () {
    $venue = availabilityVenue();
    PriceRule::factory()->for($venue)->create([
        'weekdays' => [5], 'starts_at' => '18:00', 'ends_at' => '02:00', 'ends_next_day' => true, 'price_sen' => 4000,
    ]);

    $response = $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-06&sport=badminton')->assertOk();
    $times = cellTimes($response);

    expect($times)->not->toContain('13:00') // the prayer break
        ->and(end($times))->toBe('01:30');

    $afterMidnight = collect($response->json('data.courts.0.cells'))->firstWhere('time', '00:30');
    expect($afterMidnight['starts_at'])->toBe('2026-11-07T00:30:00+08:00') // really Saturday
        ->and($afterMidnight['price_sen'])->toBe(2000); // Friday's late price
});

it('still shows last night\'s late hours just after midnight', function () {
    availabilityVenue();
    $this->travelTo('2026-11-06 16:40:00'); // Saturday 00:40 in Malaysia, still Friday night at the venue

    $response = $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-06&sport=badminton')->assertOk();

    expect(cellTimes($response))->toBe(['01:00', '01:30']);
});

it('FR-07 shows nothing to book on a closure day, and says why', function () {
    $venue = availabilityVenue();
    $venue->closures()->create(['starts_on' => '2026-11-02', 'ends_on' => '2026-11-03', 'reason' => 'Deepavali']);

    $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-02&sport=badminton')
        ->assertOk()
        ->assertJsonPath('data.closed_reason', 'Deepavali')
        ->assertJsonCount(0, 'data.courts.0.cells');
});

it('FR-13 only shows dates inside the booking window', function () {
    availabilityVenue(); // 14 days ahead

    $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-16')->assertOk();
    $this->getJson('/api/v1/venues/arena-test/availability?date=2026-11-17')
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.date.0', 'Choose a date from today to 16 Nov 2026.');
    $this->getJson('/api/v1/venues/arena-test/availability?date=2026-10-31')->assertStatus(422);
    $this->getJson('/api/v1/venues/arena-test/availability')->assertStatus(422);
});

it('hides a venue that is not approved, whatever is asked', function () {
    Venue::factory()->create(['slug' => 'not-yet']); // pending

    $this->getJson('/api/v1/venues/not-yet/availability?date=2026-11-02')->assertNotFound();
    $this->getJson('/api/v1/venues/not-yet/availability?date=nonsense')->assertNotFound();
    $this->postJson('/api/v1/venues/not-yet/quote', [])->assertNotFound();
});

it('FR-10 quotes a booking that runs into peak time, half hour by half hour', function () {
    $venue = availabilityVenue();
    PriceRule::factory()->for($venue)->create(['weekdays' => [1], 'price_sen' => 3000]); // Monday from 6:00 pm
    $court = $venue->courts()->where('name', 'Court 1')->first();

    // 5:00 pm to 7:00 pm: 2 half hours at RM 10.00 and 2 at RM 15.00.
    $this->postJson('/api/v1/venues/arena-test/quote', [
        'court_id' => $court->id, 'starts_at' => '2026-11-02T17:00:00+08:00', 'minutes' => 120,
    ])
        ->assertOk()
        ->assertJsonPath('data.date', '2026-11-02')
        ->assertJsonPath('data.ends_at', '2026-11-02T19:00:00+08:00')
        ->assertJsonPath('data.price_sen', 5000)
        ->assertJsonPath('data.price', 'RM 50.00');

    // A time without an offset is Malaysia time; 09:00 UTC would be the same 5:00 pm.
    $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'starts_at' => '2026-11-02 17:00', 'minutes' => 120])
        ->assertJsonPath('data.price_sen', 5000);
    $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'starts_at' => '2026-11-02T09:00:00Z', 'minutes' => 120])
        ->assertJsonPath('data.starts_at', '2026-11-02T17:00:00+08:00');
});

it('ADR-015 quotes a booking after midnight as part of Friday night', function () {
    $venue = availabilityVenue();
    PriceRule::factory()->for($venue)->create([
        'weekdays' => [5], 'starts_at' => '18:00', 'ends_at' => '02:00', 'ends_next_day' => true, 'price_sen' => 4000,
    ]);
    $court = $venue->courts()->where('name', 'Court 1')->first();

    $this->postJson('/api/v1/venues/arena-test/quote', [
        'court_id' => $court->id, 'starts_at' => '2026-11-07T00:30:00+08:00', 'minutes' => 60,
    ])
        ->assertOk()
        ->assertJsonPath('data.date', '2026-11-06')
        ->assertJsonPath('data.price', 'RM 40.00');
});

it('explains why a booking can\'t be quoted', function (array $input, string $field, string $message) {
    $venue = availabilityVenue();
    $court = $venue->courts()->where('name', 'Court 1')->first();

    $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'minutes' => 60, ...$input])
        ->assertStatus(422)
        ->assertJsonPath("error.details.fields.{$field}.0", $message);
})->with([
    'before opening' => [['starts_at' => '2026-11-02T07:00:00+08:00'], 'starts_at', 'The venue is closed at that time.'],
    'already started' => [['starts_at' => '2026-11-02T09:30:00+08:00'], 'starts_at', 'That time has already started. Choose a later time.'],
    'not on the half hour' => [['starts_at' => '2026-11-02T18:15:00+08:00'], 'starts_at', 'Choose a time on the hour or half hour.'],
    'past closing' => [['starts_at' => '2026-11-02T22:00:00+08:00', 'minutes' => 120], 'minutes', 'The venue closes at 11:00 pm. Choose a shorter booking or an earlier time.'],
    'into the prayer break' => [['starts_at' => '2026-11-06T12:00:00+08:00', 'minutes' => 60], 'minutes', 'The venue closes at 12:30 pm. Choose a shorter booking or an earlier time.'],
    'past midnight on Sunday' => [['starts_at' => '2026-11-08T23:30:00+08:00', 'minutes' => 60], 'minutes', 'The venue closes at midnight. Choose a shorter booking or an earlier time.'],
    'past Friday night' => [['starts_at' => '2026-11-07T01:30:00+08:00', 'minutes' => 60], 'minutes', 'The venue closes at 2:00 am. Choose a shorter booking or an earlier time.'],
    'beyond the window' => [['starts_at' => '2026-11-20T10:00:00+08:00'], 'starts_at', 'You can book up to 16 Nov 2026.'],
    'not whole slots' => [['starts_at' => '2026-11-02T18:00:00+08:00', 'minutes' => 90], 'minutes', 'Bookings here are 60 to 180 minutes long, in 60-minute steps.'],
    'too long' => [['starts_at' => '2026-11-02T12:00:00+08:00', 'minutes' => 240], 'minutes', 'Bookings here are 60 to 180 minutes long, in 60-minute steps.'],
]);

it('FR-13 explains the venue\'s notice when a booking is too soon', function () {
    $venue = availabilityVenue(['min_notice_minutes' => 120]);
    $court = $venue->courts()->where('name', 'Court 1')->first();

    $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'starts_at' => '2026-11-02T11:00:00+08:00', 'minutes' => 60])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.starts_at.0', 'That is too soon. This venue takes bookings until 2 hours before the start.');
    $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'starts_at' => '2026-11-02T12:00:00+08:00', 'minutes' => 60])
        ->assertOk();
});

it('refuses a court that players can\'t book, or that belongs to another venue', function () {
    $venue = availabilityVenue();
    $unpriced = Court::factory()->for($venue)->offering('badminton')->unpriced()->create();
    $otherVenuesCourt = Court::factory()->for(Venue::factory()->active())->create();

    foreach ([$unpriced, $otherVenuesCourt] as $court) {
        $this->postJson('/api/v1/venues/arena-test/quote', ['court_id' => $court->id, 'starts_at' => '2026-11-02T18:00:00+08:00', 'minutes' => 60])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.court_id.0', 'This court can\'t be booked.');
    }
});
