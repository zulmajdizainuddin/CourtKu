<?php

use App\Enums\BookingSource;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\CarbonImmutable;

/*
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC). The venue is open every day 08:00–23:00.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

function myVenue(array $settings = []): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'my-arena', 'phone' => '+60312345678', ...$settings]);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return $venue;
}

/** A booking made through the real engine, e.g. myBooking($venue, $player, '2026-11-03T18:00:00+08:00'). */
function myBooking(Venue $venue, User $player, string $startsAt, string $court = 'Court 1'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->where('name', $court)->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
    );
}

it('FR-30 lists a player\'s upcoming bookings soonest first, and past ones latest first', function () {
    $venue = myVenue();
    $player = User::factory()->create();
    $played = myBooking($venue, $player, '2026-11-02T11:00:00+08:00');
    $tomorrow = myBooking($venue, $player, '2026-11-03T18:00:00+08:00');
    $today = myBooking($venue, $player, '2026-11-02T20:00:00+08:00');
    $cancelled = myBooking($venue, $player, '2026-11-04T18:00:00+08:00');
    $this->actingAs($player)->postJson("/api/v1/bookings/{$cancelled->code}/cancel")->assertOk();
    myBooking($venue, User::factory()->create(), '2026-11-05T18:00:00+08:00'); // someone else's

    $this->travelTo('2026-11-02 05:00:00'); // 1:00 pm: the 11 am game is over

    $this->actingAs($player)->getJson('/api/v1/me/bookings')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.code', $today->code)
        ->assertJsonPath('data.1.code', $tomorrow->code);

    $this->actingAs($player)->getJson('/api/v1/me/bookings?when=past')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.code', $cancelled->code) // cancelled ones are history too
        ->assertJsonPath('data.0.status', 'cancelled')
        ->assertJsonPath('data.1.code', $played->code);

    $this->actingAs($player)->getJson('/api/v1/me/bookings?when=soon')->assertStatus(422);
});

it('needs a login to see bookings', function () {
    $this->getJson('/api/v1/me/bookings')->assertUnauthorized();
    $this->postJson('/api/v1/bookings/CK-ABCDE/cancel')->assertUnauthorized();
});

it('plan section 8: gives long lists 20 at a time, with a cursor to the next page', function () {
    $venue = myVenue();
    $player = User::factory()->create();
    foreach (range(11, 21) as $hour) {
        myBooking($venue, $player, "2026-11-03T{$hour}:00:00+08:00");
        myBooking($venue, $player, "2026-11-03T{$hour}:00:00+08:00", 'Court 2');
    }

    $first = $this->actingAs($player)->getJson('/api/v1/me/bookings')->assertJsonCount(20, 'data');
    $cursor = $first->json('meta.next_cursor');
    expect($cursor)->not->toBeNull();

    $this->actingAs($player)->getJson('/api/v1/me/bookings?cursor='.$cursor)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.next_cursor', null);
});

it('FR-24 lets a player cancel before the venue\'s cutoff, and frees the time', function () {
    $venue = myVenue(); // 24 hours
    $player = User::factory()->create();
    $booking = myBooking($venue, $player, '2026-11-03T18:00:00+08:00'); // in 32 hours

    $this->actingAs($player)->getJson("/api/v1/bookings/{$booking->code}")
        ->assertJsonPath('data.can_cancel', true)
        ->assertJsonPath('data.cancel_until', '2026-11-02T18:00:00+08:00');

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('data.can_cancel', false)
        ->assertJsonPath('data.cancelled_at', '2026-11-02T10:00:00+08:00');

    $log = AuditLog::where('action', 'booking.cancelled')->sole();
    expect($log->user_id)->toBe($player->id)
        ->and($log->changes['reason'])->toBe('Cancelled by the player');

    // Someone else can book the time now.
    expect(fn () => myBooking($venue, User::factory()->create(), '2026-11-03T18:00:00+08:00'))->not->toThrow(Exception::class);
});

it('FR-24 refuses to cancel online after the cutoff, and points to the venue', function () {
    $venue = myVenue(); // 24 hours
    $player = User::factory()->create();
    $booking = myBooking($venue, $player, '2026-11-02T18:00:00+08:00'); // in 8 hours

    $this->actingAs($player)->getJson("/api/v1/bookings/{$booking->code}")->assertJsonPath('data.can_cancel', false);
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'TOO_LATE_TO_CANCEL')
        ->assertJsonPath('error.message', 'Online cancelling closed 24 hours before the start. Please call the venue on +60312345678.');

    // With a 2-hour cutoff the same booking can still be cancelled.
    $venue->update(['cancel_cutoff_hours' => 2]);
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();
});

it('cancels a booking only once, and only the player\'s own', function () {
    $venue = myVenue();
    $player = User::factory()->create();
    $booking = myBooking($venue, $player, '2026-11-04T18:00:00+08:00');

    $this->actingAs(User::factory()->create())->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertNotFound();

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INVALID_BOOKING_STATUS');
});

it('FR-24 lets the owner choose the cutoff', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", [
        'slot_minutes' => 60, 'min_minutes' => 60, 'max_minutes' => 180, 'booking_window_days' => 14,
        'min_notice_minutes' => 0, 'cancel_cutoff_hours' => 48,
    ])
        ->assertOk()
        ->assertJsonPath('data.cancel_cutoff_hours', 48);
});
