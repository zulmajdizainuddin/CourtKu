<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;

/*
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC).
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** An approved venue open Monday 08:00–23:00: a badminton court (RM 20.00 an hour) and a futsal pitch. */
function onlineVenue(): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'online-arena', 'phone' => '+60312345678']);
    $venue->operatingHours()->create(['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    Court::factory()->for($venue)->offering('futsal')->create(['name' => 'Pitch', 'base_price_sen' => 8000]);

    return $venue;
}

function courtNamed(Venue $venue, string $name): Court
{
    return $venue->courts()->where('name', $name)->firstOrFail();
}

/** A player's Book press: the booking and its Idempotency-Key header. */
function bookOnline($test, User $player, Venue $venue, array $input = [], ?string $key = 'press-1')
{
    $body = [
        'court_id' => courtNamed($venue, 'Court 1')->id,
        'sport' => 'badminton',
        'starts_at' => '2026-11-02T18:00:00+08:00',
        'minutes' => 60,
        ...$input,
    ];

    return $test->actingAs($player)->withHeaders($key === null ? [] : ['Idempotency-Key' => $key])
        ->postJson("/api/v1/venues/{$venue->slug}/bookings", $body);
}

it('FR-15 lets a verified player book a court, paid at the venue', function () {
    $venue = onlineVenue();
    $player = User::factory()->create();

    bookOnline($this, $player, $venue)
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.venue.name', $venue->name)
        ->assertJsonPath('data.court.name', 'Court 1')
        ->assertJsonPath('data.sport', 'badminton')
        ->assertJsonPath('data.starts_at', '2026-11-02T18:00:00+08:00')
        ->assertJsonPath('data.ends_at', '2026-11-02T19:00:00+08:00')
        ->assertJsonPath('data.minutes', 60)
        ->assertJsonPath('data.total', 'RM 20.00')
        ->assertJsonPath('data.pay_at_venue', true);

    $booking = Booking::sole();
    expect($booking->user_id)->toBe($player->id)
        ->and($booking->source)->toBe(BookingSource::Online)
        ->and($booking->status)->toBe(BookingStatus::Confirmed);

    // The time is gone for everyone else.
    $this->getJson('/api/v1/venues/online-arena/availability?date=2026-11-02&sport=badminton')
        ->assertJsonPath('data.courts.0.cells.16.time', '18:00')
        ->assertJsonPath('data.courts.0.cells.16.available', false);
});

it('plan section 7: the same Book press twice gives the same booking, not two', function () {
    $venue = onlineVenue();
    $player = User::factory()->create();

    $first = bookOnline($this, $player, $venue)->assertCreated()->json('data.code');
    // A double tap or a network retry sends the same key again.
    $again = bookOnline($this, $player, $venue)->assertOk()->json('data.code');

    expect($again)->toBe($first)
        ->and(Booking::count())->toBe(1);

    // A new press (new key) for the same time is a real second booking: refused, the time is taken.
    bookOnline($this, $player, $venue, key: 'press-2')->assertStatus(409)->assertJsonPath('error.code', 'SLOT_TAKEN');
});

it('needs an Idempotency-Key with every booking', function () {
    bookOnline($this, User::factory()->create(), onlineVenue(), key: null)
        ->assertStatus(400)
        ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_REQUIRED');
});

it('FR-35 FR-36 needs an account with a verified email', function () {
    $venue = onlineVenue();

    $this->withHeaders(['Idempotency-Key' => 'guest'])->postJson('/api/v1/venues/online-arena/bookings', [])->assertUnauthorized();
    bookOnline($this, User::factory()->unverified()->create(), $venue)->assertForbidden();

    expect(Booking::count())->toBe(0);
});

it('refuses a time someone else has just booked, and says which half hours', function () {
    $venue = onlineVenue();
    bookOnline($this, User::factory()->create(), $venue)->assertCreated();

    bookOnline($this, User::factory()->create(), $venue, ['starts_at' => '2026-11-02T17:30:00+08:00'])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'SLOT_TAKEN')
        ->assertJsonPath('error.details.slots', ['2026-11-02T18:00:00+08:00']);
});

it('checks the court, the sport and the time', function (Closure $input, string $field, string $message) {
    $venue = onlineVenue();

    bookOnline($this, User::factory()->create(), $venue, $input($venue))
        ->assertStatus(422)
        ->assertJsonPath("error.details.fields.{$field}.0", $message);
})->with([
    'a sport the court has no' => [fn () => ['sport' => 'futsal'], 'sport', "Court 1 isn't for futsal."],
    'another venue\'s court' => [fn () => ['court_id' => Court::factory()->for(Venue::factory()->active())->create()->id], 'court_id', "This court can't be booked."],
    'a court players can\'t book' => [fn (Venue $v) => ['court_id' => tap(courtNamed($v, 'Court 1'))->update(['is_active' => false])->id], 'court_id', "This court can't be booked."],
    'a time that has started' => [fn () => ['starts_at' => '2026-11-02T09:30:00+08:00'], 'starts_at', 'That time has already started. Choose a later time.'],
    'past closing' => [fn () => ['starts_at' => '2026-11-02T22:30:00+08:00'], 'minutes', 'The venue closes at 11:00 pm. Choose a shorter booking or an earlier time.'],
    'not whole slots' => [fn () => ['minutes' => 90], 'minutes', 'Bookings here are 60 to 180 minutes long, in 60-minute steps.'],
]);

it('hides venues that are not approved', function () {
    $venue = onlineVenue();
    $venue->suspend();

    bookOnline($this, User::factory()->create(), $venue)->assertNotFound();
});

it('shows players their own booking, and nobody else\'s', function () {
    $venue = onlineVenue();
    $player = User::factory()->create();
    $code = bookOnline($this, $player, $venue)->json('data.code');

    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertOk()
        ->assertJsonPath('data.code', $code)
        ->assertJsonPath('data.venue.phone', '+60312345678');

    $this->actingAs(User::factory()->create())->getJson("/api/v1/bookings/{$code}")->assertNotFound();
    $this->actingAs($player)->getJson('/api/v1/bookings/CK-NOPE2')->assertNotFound();
});

it('limits a player to 10 booking tries a minute', function () {
    $venue = onlineVenue();
    $player = User::factory()->create();

    foreach (range(1, 10) as $i) {
        bookOnline($this, $player, $venue, key: "try-{$i}");
    }

    bookOnline($this, $player, $venue, key: 'try-11')->assertStatus(429);
});
