<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BlockService;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/*
| ADR-028: the public time grid is kept for 30 seconds, and thrown away at once when anything that
| shapes it changes. Now is Monday 2 Nov 2026, 10:00 in Malaysia; the grid shown is Wednesday's.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, Venue} the owner and the venue: open 08:00–23:00 every day, two courts at RM 20.00 an hour */
function cacheVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'cache-arena']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1', 'sort_order' => 1], ['name' => 'Court 2', 'sort_order' => 2])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $venue];
}

/** Wednesday's grid, as players see it. */
function cacheGrid(): array
{
    return test()->getJson('/api/v1/venues/cache-arena/availability?date=2026-11-04&sport=badminton')->assertOk()->json('data');
}

function cacheCell(array $grid, string $court, string $time): array
{
    return collect(collect($grid['courts'])->firstWhere('name', $court)['cells'])->firstWhere('time', $time);
}

function cacheBook(Venue $venue, string $time = '18:00'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->where('name', 'Court 1')->firstOrFail(), CarbonImmutable::parse("2026-11-04T{$time}:00+08:00"), 60),
        BookingSource::Online,
        player: User::factory()->create(),
    );
}

it('ADR-028 keeps the grid for 30 seconds when nothing changes', function () {
    [, $venue] = cacheVenue();
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['price_sen'])->toBe(1000);

    // A change made behind CourtKu's back (a plain database update sends no "changed" signal)...
    DB::table('courts')->where('venue_id', $venue->id)->update(['base_price_sen' => 3000]);

    // ...isn't seen while the kept grid is fresh...
    $this->travel(29)->seconds();
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['price_sen'])->toBe(1000);

    // ...and is seen once it is older than 30 seconds.
    $this->travel(2)->seconds();
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['price_sen'])->toBe(1500);
});

it('ADR-028 shows a new booking at once, and the time free again at once after a cancel', function () {
    [, $venue] = cacheVenue();
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['available'])->toBeTrue();

    $booking = cacheBook($venue);
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['available'])->toBeFalse();

    (new BookingStateMachine)->transition($booking, BookingStatus::Cancelled, null, 'Cancelled by staff');
    expect(cacheCell(cacheGrid(), 'Court 1', '18:00')['available'])->toBeTrue();
});

it('ADR-028 shows blocks for repairs at once, and lifting them', function () {
    [$owner, $venue] = cacheVenue();
    cacheGrid();

    $blocks = (new BlockService)->block($venue, $venue->courts()->get(), CarbonImmutable::parse('2026-11-04T09:00:00+08:00'), 60, 'Floor polishing', $owner);
    expect(cacheCell(cacheGrid(), 'Court 2', '09:00')['available'])->toBeFalse();

    foreach ($blocks as $block) {
        (new BlockService)->lift($block);
    }
    expect(cacheCell(cacheGrid(), 'Court 2', '09:00')['available'])->toBeTrue();
});

it('ADR-028 shows the owner\'s changes at once', function (Closure $change) {
    [$owner, $venue] = cacheVenue();
    $before = cacheGrid();

    $change($this, $owner, $venue);

    expect(cacheGrid())->not->toBe($before);
})->with([
    'a closed date' => [function ($test, $owner, Venue $venue) {
        $venue->closures()->create(['starts_on' => '2026-11-04', 'ends_on' => '2026-11-04', 'reason' => 'Hari Raya']);
    }],
    'new opening hours' => [function ($test, $owner, Venue $venue) {
        $test->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", [
            'periods' => array_map(fn (int $day) => ['weekday' => $day, 'opens_at' => '10:00', 'closes_at' => '22:00'], range(0, 6)),
        ])->assertOk();
    }],
    'closed every day' => [function ($test, $owner, Venue $venue) {
        $test->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => []])->assertOk();
    }],
    'a court price' => [function ($test, $owner, Venue $venue) {
        $venue->courts()->where('name', 'Court 1')->firstOrFail()->update(['base_price_sen' => 2500]);
    }],
    'a court switched off' => [function ($test, $owner, Venue $venue) {
        $venue->courts()->where('name', 'Court 2')->firstOrFail()->update(['is_active' => false]);
    }],
    'the court order' => [function ($test, $owner, Venue $venue) {
        $test->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/order", [
            'court_ids' => $venue->courts()->orderByDesc('sort_order')->pluck('id')->all(),
        ])->assertOk();
    }],
    'a peak price' => [function ($test, $owner, Venue $venue) {
        $venue->priceRules()->create(['name' => 'Evening', 'weekdays' => [3], 'starts_at' => '18:00', 'ends_at' => '23:00', 'price_sen' => 3000]);
    }],
    'the booking settings' => [function ($test, $owner, Venue $venue) {
        $venue->update(['min_minutes' => 120, 'max_minutes' => 240]);
    }],
]);

it('ADR-028 keeps each day and sport apart', function () {
    [, $venue] = cacheVenue();
    $wednesday = cacheGrid();

    $thursday = $this->getJson('/api/v1/venues/cache-arena/availability?date=2026-11-05&sport=badminton')->json('data');
    $everything = $this->getJson('/api/v1/venues/cache-arena/availability?date=2026-11-04')->json('data');

    expect($thursday['date'])->toBe('2026-11-05')
        ->and($wednesday['date'])->toBe('2026-11-04')
        ->and($everything['courts'])->toHaveCount(2);
});
