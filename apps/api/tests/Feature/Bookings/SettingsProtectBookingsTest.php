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
| FR-38: settings never strand bookings.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC).
| The venue is open Monday 08:00–23:00 and Friday 18:00–02:00.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, Venue} the owner (with two-factor) and the venue */
function protectVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create();
    $venue->operatingHours()->createMany([
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '18:00', 'closes_at' => '02:00', 'closes_next_day' => true],
    ]);
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $venue];
}

function protectBooking(Venue $venue, string $startsAt, string $court = 'Court 1'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->where('name', $court)->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: User::factory()->create(['name' => 'Siti']),
    );
}

/** The whole week as the hours form sends it, with Monday and Friday as given. */
function week(array $monday, array $friday = ['18:00', '02:00']): array
{
    return ['periods' => [
        ['weekday' => 1, 'opens_at' => $monday[0], 'closes_at' => $monday[1]],
        ['weekday' => 5, 'opens_at' => $friday[0], 'closes_at' => $friday[1]],
    ]];
}

it('FR-38 refuses new hours that would leave a booking outside them, and lists it', function () {
    [$owner, $venue] = protectVenue();
    $booking = protectBooking($venue, '2026-11-02T20:00:00+08:00'); // Monday 8–9 pm

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", week(['08:00', '20:00']))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'BOOKINGS_CONFLICT')
        ->assertJsonPath('error.message', '1 upcoming booking would be outside the new opening hours. Cancel it first from the Schedule, then save the hours again.')
        ->assertJsonPath('error.details.bookings.0', [
            'code' => $booking->code, 'court' => 'Court 1', 'starts_at' => '2026-11-02T20:00:00+08:00',
            'business_date' => '2026-11-02', 'customer' => 'Siti',
        ]);

    // Nothing changed.
    expect($venue->operatingHours()->where('weekday', 1)->value('closes_at'))->toStartWith('23:00');

    // Closing right when the booking ends is fine.
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", week(['08:00', '21:00']))->assertOk();
});

it('refuses closing a whole day that has bookings', function () {
    [$owner, $venue] = protectVenue();
    protectBooking($venue, '2026-11-06T19:00:00+08:00'); // Friday

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => [
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00'],
    ]])->assertStatus(409);
});

it('ADR-015 checks the hours after midnight on the night they belong to', function () {
    [$owner, $venue] = protectVenue();
    protectBooking($venue, '2026-11-07T00:30:00+08:00'); // Saturday 00:30 = Friday night

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", week(['08:00', '23:00'], ['18:00', '00:00']))
        ->assertStatus(409)
        ->assertJsonPath('error.details.bookings.0.business_date', '2026-11-06');
});

it('ignores bookings that are over, cancelled or no-shows', function () {
    [$owner, $venue] = protectVenue();
    $cancelled = protectBooking($venue, '2026-11-02T20:00:00+08:00');
    (new BookingStateMachine)->transition($cancelled, BookingStatus::Cancelled, $owner, 'Rain');
    $missed = protectBooking($venue, '2026-11-02T21:00:00+08:00');
    (new BookingStateMachine)->transition($missed, BookingStatus::NoShow, $owner);
    protectBooking($venue, '2026-11-02T11:00:00+08:00');
    $this->travelTo('2026-11-02 04:00:00'); // 12:00, the 11 am game is over

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", week(['12:00', '18:00']))->assertOk();
});

it('FR-38 refuses a closure on days with bookings, and allows it on other days', function () {
    [$owner, $venue] = protectVenue();
    $booking = protectBooking($venue, '2026-11-06T19:00:00+08:00');

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2026-11-05', 'ends_on' => '2026-11-07', 'reason' => 'Renovation',
    ])
        ->assertStatus(409)
        ->assertJsonPath('error.message', '1 upcoming booking is on these days. Cancel it first from the Schedule, then add the closure again.')
        ->assertJsonPath('error.details.bookings.0.code', $booking->code);

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2026-11-09', 'ends_on' => '2026-11-10', 'reason' => 'Renovation',
    ])->assertCreated();
});

it('FR-38 refuses switching off or unpricing a court with upcoming bookings, but allows other edits', function () {
    [$owner, $venue] = protectVenue();
    protectBooking($venue, '2026-11-02T20:00:00+08:00');
    $court = $venue->courts()->where('name', 'Court 1')->firstOrFail();
    $url = "/api/v1/manage/venues/{$venue->id}/courts/{$court->id}";
    $edit = fn (array $changes) => ['name' => 'Court 1', 'is_indoor' => true, 'sports' => ['badminton'], ...$changes];

    $this->actingAs($owner)->putJson($url, $edit(['is_active' => false]))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'BOOKINGS_CONFLICT');
    $this->actingAs($owner)->putJson($url, $edit(['base_price_sen' => null]))->assertStatus(409);
    expect($court->fresh()->is_active)->toBeTrue();

    // Renaming or a new price is fine: the booking keeps its price (it was saved with it).
    $this->actingAs($owner)->putJson($url, $edit(['name' => 'Court A', 'base_price_sen' => 3000]))->assertOk();
    // A court with no upcoming bookings can be switched off.
    $court2 = $venue->courts()->where('name', 'Court 2')->firstOrFail();
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court2->id}", [
        'name' => 'Court 2', 'is_indoor' => true, 'sports' => ['badminton'], 'is_active' => false,
    ])->assertOk();
});

it('lets the booking settings change, because existing bookings keep their own time and price', function () {
    [$owner, $venue] = protectVenue();
    protectBooking($venue, '2026-11-02T20:00:00+08:00');

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", [
        'slot_minutes' => 30, 'min_minutes' => 90, 'max_minutes' => 180, 'booking_window_days' => 7,
        'min_notice_minutes' => 120, 'cancel_cutoff_hours' => 48,
    ])->assertOk();
});
