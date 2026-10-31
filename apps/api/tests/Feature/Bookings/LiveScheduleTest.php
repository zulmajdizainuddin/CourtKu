<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use Carbon\CarbonImmutable;

/*
| FR-14: the Schedule tab asks GET .../schedule/version every 10 seconds and reloads when it changes.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, User, Venue} the owner, a staff member and the venue, open 08:00–23:00 every day */
function liveVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $staff = User::factory()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create();
    $venue->members()->attach($staff, ['role' => 'staff']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);

    return [$owner, $staff, $venue];
}

function liveVersion(User $user, Venue $venue): string
{
    return test()->actingAs($user)->getJson("/api/v1/manage/venues/{$venue->id}/schedule/version")->assertOk()->json('data.version');
}

it('FR-14 gives staff a version that stays the same while nothing changes', function () {
    [, $staff, $venue] = liveVenue();

    $first = liveVersion($staff, $venue);

    expect($first)->toBeString()->not->toBe('')
        ->and(liveVersion($staff, $venue))->toBe($first);
});

it('FR-14 changes the version when a player books or cancels, so the schedule reloads', function () {
    [, $staff, $venue] = liveVenue();
    $player = User::factory()->create();
    $before = liveVersion($staff, $venue);

    $service = new BookingService;
    $booking = $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse('2026-11-02T18:00:00+08:00'), 60),
        BookingSource::Online,
        player: $player,
    );
    $afterBooking = liveVersion($staff, $venue);
    expect($afterBooking)->not->toBe($before);

    (new BookingStateMachine)->transition($booking, BookingStatus::Cancelled, $player, 'Cancelled by the player');
    expect(liveVersion($staff, $venue))->not->toBe($afterBooking);
});

it('only lets the venue\'s own team ask', function () {
    [, , $venue] = liveVenue();

    $this->getJson("/api/v1/manage/venues/{$venue->id}/schedule/version")->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson("/api/v1/manage/venues/{$venue->id}/schedule/version")->assertNotFound();
});
