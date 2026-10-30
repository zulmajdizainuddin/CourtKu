<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use Carbon\CarbonImmutable;

/*
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC).
| The venue is open Monday 08:00–23:00 and Friday 18:00–02:00.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, Venue} the owner (with two-factor) and the venue */
function scheduleVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'schedule-arena']);
    $venue->operatingHours()->createMany([
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '18:00', 'closes_at' => '02:00', 'closes_next_day' => true],
    ]);
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $venue];
}

function scheduleBooking(Venue $venue, User $player, string $startsAt, int $minutes = 60, string $court = 'Court 1'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->where('name', $court)->firstOrFail(), CarbonImmutable::parse($startsAt), $minutes),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
    );
}

it('FR-31 shows every court and booking of the day, with the player\'s name, phone and email', function () {
    [$owner, $venue] = scheduleVenue();
    $player = User::factory()->create(['name' => 'Siti Aminah', 'phone' => '+60123456789', 'email' => 'siti@example.com']);
    $booking = scheduleBooking($venue, $player, '2026-11-02T18:00:00+08:00', 120);
    scheduleBooking($venue, $player, '2026-11-02T20:00:00+08:00', 60, 'Court 2');

    $response = $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertOk()
        ->assertJsonPath('data.date', '2026-11-02')
        ->assertJsonPath('data.courts.0.name', 'Court 1')
        ->assertJsonPath('data.courts.1.name', 'Court 2')
        ->assertJsonCount(2, 'data.bookings')
        ->assertJsonPath('data.bookings.0.code', $booking->code)
        ->assertJsonPath('data.bookings.0.starts_at', '2026-11-02T18:00:00+08:00')
        ->assertJsonPath('data.bookings.0.minutes', 120)
        ->assertJsonPath('data.bookings.0.total', 'RM 40.00')
        ->assertJsonPath('data.bookings.0.status', 'confirmed')
        ->assertJsonPath('data.bookings.0.customer', ['name' => 'Siti Aminah', 'phone' => '+60123456789', 'email' => 'siti@example.com']);

    // Every half hour from opening (8:00 am) to the last one before closing (10:30 pm).
    $times = array_column($response->json('data.times'), 'time');
    expect($times)->toHaveCount(30)
        ->and($times[0])->toBe('08:00')
        ->and(end($times))->toBe('22:30');
});

it('defaults to today in Malaysia, and leaves out cancelled bookings and other days', function () {
    [$owner, $venue] = scheduleVenue();
    $player = User::factory()->create();
    scheduleBooking($venue, $player, '2026-11-02T18:00:00+08:00');
    $cancelled = scheduleBooking($venue, $player, '2026-11-02T20:00:00+08:00');
    (new BookingStateMachine)->transition($cancelled, BookingStatus::Cancelled, $player);
    scheduleBooking($venue, $player, '2026-11-06T19:00:00+08:00'); // Friday

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule")
        ->assertJsonPath('data.date', '2026-11-02')
        ->assertJsonCount(1, 'data.bookings');
});

it('ADR-015 keeps a booking after midnight on the night it belongs to', function () {
    [$owner, $venue] = scheduleVenue();
    $late = scheduleBooking($venue, User::factory()->create(), '2026-11-07T00:30:00+08:00'); // Saturday 00:30

    $friday = $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-06")
        ->assertJsonPath('data.bookings.0.code', $late->code);

    $lastTime = collect($friday->json('data.times'))->last();
    expect($lastTime)->toBe(['starts_at' => '2026-11-07T01:30:00+08:00', 'time' => '01:30', 'next_day' => true]);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-07")
        ->assertJsonCount(0, 'data.bookings');
});

it('shows a court that was switched off only on days it still has bookings', function () {
    [$owner, $venue] = scheduleVenue();
    scheduleBooking($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00', 60, 'Court 2');
    $venue->courts()->where('name', 'Court 2')->update(['is_active' => false]);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertJsonCount(2, 'data.courts')
        ->assertJsonPath('data.courts.1.is_active', false);
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-03")
        ->assertJsonCount(1, 'data.courts');
});

it('says why the venue is closed on a closure day', function () {
    [$owner, $venue] = scheduleVenue();
    $venue->closures()->create(['starts_on' => '2026-11-02', 'ends_on' => '2026-11-02', 'reason' => 'Deepavali']);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertJsonPath('data.closed_reason', 'Deepavali')
        ->assertJsonCount(0, 'data.times');
});

it('lets staff see the schedule too, and checks the date', function () {
    [, $venue] = scheduleVenue();
    $staff = User::factory()->create();
    $venue->members()->attach($staff, ['role' => 'staff']);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/schedule")->assertOk();
    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2-11-2026")->assertStatus(422);
});
