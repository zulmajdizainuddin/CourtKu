<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\CarbonImmutable;

/*
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC). The venue is open 08:00–23:00 every day.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, User, Venue} the owner (with two-factor), a staff member and the venue */
function actionsVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $staff = User::factory()->create(['name' => 'Staff One']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create();
    $venue->members()->attach($staff, ['role' => 'staff']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);

    return [$owner, $staff, $venue];
}

/** A player's online booking at this venue, e.g. today at 11:00. */
function actionsBooking(Venue $venue, string $startsAt = '2026-11-02T11:00:00+08:00'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: User::factory()->create(),
    );
}

function actionUrl(Venue $venue, Booking $booking, string $action): string
{
    return "/api/v1/manage/venues/{$venue->id}/bookings/{$booking->code}/{$action}";
}

it('FR-28 checks a player in, from an hour before the start', function () {
    [, $staff, $venue] = actionsVenue();
    $booking = actionsBooking($venue); // 11:00, now 10:00

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/schedule")
        ->assertJsonPath('data.bookings.0.actions', ['check_in' => true, 'no_show' => true, 'cancel' => true, 'mark_paid' => true]);

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'check-in'))
        ->assertOk()
        ->assertJsonPath('data.status', 'checked_in')
        ->assertJsonPath('data.checked_in_at', '2026-11-02T10:00:00+08:00')
        ->assertJsonPath('data.actions.check_in', false);

    expect(AuditLog::where('action', 'booking.checked_in')->where('user_id', $staff->id)->exists())->toBeTrue();
});

it('refuses to check in too early or after the booking ended', function () {
    [, $staff, $venue] = actionsVenue();
    $later = actionsBooking($venue, '2026-11-02T18:00:00+08:00');

    $this->actingAs($staff)->postJson(actionUrl($venue, $later, 'check-in'))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'CHECK_IN_NOT_OPEN')
        ->assertJsonPath('error.message', 'Check-in opens 1 hour before the start, at 2 Nov, 5:00 pm.');

    $this->travelTo('2026-11-02 11:00:00'); // 7:00 pm, the 6 pm booking is over
    $this->actingAs($staff)->postJson(actionUrl($venue, $later, 'check-in'))
        ->assertStatus(409)
        ->assertJsonPath('error.message', "This booking has already ended, so it can't be checked in. Mark it as a no-show instead.");
});

it('marks a no-show at any time, and keeps the time taken as history', function () {
    [, $staff, $venue] = actionsVenue();
    $booking = actionsBooking($venue, '2026-11-02T18:00:00+08:00'); // hours away: the owner's choice allows it

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'no-show'))
        ->assertOk()
        ->assertJsonPath('data.status', 'no_show');

    expect(BookingSlot::where('booking_id', $booking->id)->count())->toBe(2);
});

it('FR-28 records payment at the counter: how, when and by whom, only once', function () {
    [, $staff, $venue] = actionsVenue();
    $booking = actionsBooking($venue);

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'paid'), ['method' => 'qr'])
        ->assertOk()
        ->assertJsonPath('data.payment_method', 'qr')
        ->assertJsonPath('data.paid_at', '2026-11-02T10:00:00+08:00')
        ->assertJsonPath('data.paid_by', 'Staff One')
        ->assertJsonPath('data.actions.mark_paid', false);

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'paid'), ['method' => 'cash'])
        ->assertStatus(409)
        ->assertJsonPath('error.message', 'This booking is already paid.');

    expect(AuditLog::where('action', 'booking.paid')->sole()->changes)->toEqualCanonicalizing(['method' => 'qr', 'total_sen' => 2000]);
});

it('checks the payment method, and refuses to mark a cancelled booking paid', function () {
    [, $staff, $venue] = actionsVenue();
    $booking = actionsBooking($venue);

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'paid'), ['method' => 'bitcoin'])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.method.0', 'Choose cash, QR / DuitNow or card.');

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'cancel'), ['reason' => 'Rain'])->assertOk();
    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'paid'), ['method' => 'cash'])
        ->assertStatus(409)
        ->assertJsonPath('error.message', "This booking is cancelled, so it can't be paid.");
});

it('lets staff cancel any booking with a reason, with no cutoff, and frees the time', function () {
    [, $staff, $venue] = actionsVenue();
    $booking = actionsBooking($venue); // in an hour: a player could no longer cancel it online

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'cancel'))
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.reason.0', 'Say why, e.g. rain or the player asked.');

    $this->actingAs($staff)->postJson(actionUrl($venue, $booking, 'cancel'), ['reason' => 'Player called, sick'])
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('data.cancel_reason', 'Player called, sick');

    expect(BookingSlot::count())->toBe(0)
        ->and(AuditLog::where('action', 'booking.cancelled')->sole()->user_id)->toBe($staff->id);
});

it('moves a booking only along the allowed steps', function () {
    [$owner, , $venue] = actionsVenue();
    $booking = actionsBooking($venue);
    $this->actingAs($owner)->postJson(actionUrl($venue, $booking, 'no-show'))->assertOk();

    $this->actingAs($owner)->postJson(actionUrl($venue, $booking, 'check-in'))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INVALID_BOOKING_STATUS');
    $this->actingAs($owner)->postJson(actionUrl($venue, $booking, 'cancel'), ['reason' => 'Oops'])
        ->assertStatus(409);
    // A no-show can still pay, if the venue charges it.
    $this->actingAs($owner)->postJson(actionUrl($venue, $booking, 'paid'), ['method' => 'cash'])->assertOk();
});

it('ADR-002 never reaches another venue\'s booking through this venue\'s address', function () {
    [$owner, , $venue] = actionsVenue();
    [, , $otherVenue] = actionsVenue();
    $theirs = actionsBooking($otherVenue);

    $this->actingAs($owner)->postJson(actionUrl($venue, $theirs, 'cancel'), ['reason' => 'Taken over'])->assertNotFound();

    expect($theirs->fresh()->status)->toBe(BookingStatus::Confirmed);
});
