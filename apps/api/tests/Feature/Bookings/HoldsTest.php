<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingConflicts;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Services\Holds;
use Carbon\CarbonImmutable;

/*
| Checkout holds (FR-16, ADR-021): a booking paid online keeps its half hours for the venue's
| hold time while the player pays. Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC).
| The venue is open every day 08:00–23:00.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, Venue} the owner (with two-factor) and the venue, with two courts at RM 20.00 an hour */
function holdsVenue(array $settings = []): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'holds-arena', ...$settings]);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $venue];
}

function holdsCourt(Venue $venue, string $name = 'Court 1'): Court
{
    return $venue->courts()->where('name', $name)->firstOrFail();
}

/** A booking paid online: held while the player pays (hold: true), through the real engine. */
function hold(Venue $venue, User $player, string $startsAt, string $court = 'Court 1'): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare(holdsCourt($venue, $court), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
        hold: true,
    );
}

/** A booking paid at the venue: confirmed straight away. */
function payAtVenue(Venue $venue, User $player, string $startsAt): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare(holdsCourt($venue), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
    );
}

/** Whether Court 1's half hour (e.g. "18:00") on Monday shows as free on the public booking page. */
function holdsCellFree(string $time, string $court = 'Court 1'): bool
{
    $courts = collect(test()->getJson('/api/v1/venues/holds-arena/availability?date=2026-11-02')->assertOk()->json('data.courts'));

    return collect($courts->firstWhere('name', $court)['cells'])->firstWhere('time', $time)['available'];
}

function holdsErrorCode(Closure $action): ?string
{
    try {
        $action();
    } catch (ApiException $e) {
        return $e->errorCode;
    }

    return null;
}

it('FR-16 holds the half hours while the player pays, for the venue\'s hold time', function () {
    [, $venue] = holdsVenue();
    $player = User::factory()->create();

    $booking = hold($venue, $player, '2026-11-02T18:00:00+08:00');

    expect($booking->status)->toBe(BookingStatus::PendingPayment)
        ->and($booking->fresh()->hold_expires_at->toDateTimeString())->toBe('2026-11-02 02:15:00') // 15 minutes, in UTC
        ->and($booking->slots()->count())->toBe(2)
        ->and(holdsCellFree('18:00'))->toBeFalse() // nobody else can take it meanwhile
        ->and(holdsCellFree('18:30'))->toBeFalse()
        ->and(AuditLog::where('action', 'booking.created')->first()->changes)->toHaveKey('hold_expires_at');

    // Another player trying the same time gets the usual answer.
    expect(holdsErrorCode(fn () => hold($venue, User::factory()->create(), '2026-11-02T18:30:00+08:00')))->toBe('SLOT_TAKEN');
});

it('uses each venue\'s own hold time', function () {
    [, $venue] = holdsVenue(['hold_minutes' => 20]);

    $booking = hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');

    expect($booking->fresh()->hold_expires_at->toDateTimeString())->toBe('2026-11-02 02:20:00');
});

it('confirms a booking paid at the venue straight away, with no hold', function () {
    [, $venue] = holdsVenue();

    $booking = payAtVenue($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');

    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->hold_expires_at)->toBeNull();
});

it('FR-16 lets a player wait on at most 2 unpaid bookings at once', function () {
    [, $venue] = holdsVenue();
    $player = User::factory()->create();
    hold($venue, $player, '2026-11-02T18:00:00+08:00');
    hold($venue, $player, '2026-11-02T18:00:00+08:00', 'Court 2');
    payAtVenue($venue, $player, '2026-11-02T20:00:00+08:00'); // paid at the venue: not a hold, doesn't count

    expect(holdsErrorCode(fn () => hold($venue, $player, '2026-11-02T21:00:00+08:00')))->toBe('TOO_MANY_HOLDS')
        ->and(Booking::where('user_id', $player->id)->count())->toBe(3) // the third hold wasn't saved
        ->and(hold($venue, User::factory()->create(), '2026-11-02T21:00:00+08:00')->status) // others still can
        ->toBe(BookingStatus::PendingPayment);

    // Once a hold's time is up, it no longer counts.
    $this->travel(15)->minutes();
    expect(hold($venue, $player, '2026-11-02T22:00:00+08:00')->status)->toBe(BookingStatus::PendingPayment);
});

it('FR-16 frees the time as soon as the hold runs out, even before any timer runs', function () {
    [, $venue] = holdsVenue();
    $booking = hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');

    $this->travel(14)->minutes();
    expect(holdsCellFree('18:00'))->toBeFalse(); // 1 minute left

    $this->travel(1)->minutes();
    expect(holdsCellFree('18:00'))->toBeTrue() // reading the page released it
        ->and($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($booking->slots()->count())->toBe(0);

    $expiry = AuditLog::where('action', 'booking.expired')->firstOrFail();
    expect($expiry->user_id)->toBeNull() // done by CourtKu itself
        ->and($expiry->changes)->toMatchArray(['from' => 'pending_payment', 'to' => 'expired', 'reason' => 'Not paid in time']);

    // Someone else can book the time now.
    expect(payAtVenue($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00')->status)->toBe(BookingStatus::Confirmed);
});

it('releases a hold whose time is up when someone books that time', function () {
    [, $venue] = holdsVenue();
    $first = hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');
    $this->travel(16)->minutes();

    // No page was read in between: booking itself frees the old hold first.
    $second = payAtVenue($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');

    expect($second->status)->toBe(BookingStatus::Confirmed)
        ->and($first->fresh()->status)->toBe(BookingStatus::Expired);
});

it('ADR-022 the every-minute tick expires holds and completes finished bookings, and nothing else', function () {
    [$owner, $venue] = holdsVenue();
    $player = User::factory()->create();
    $late = hold($venue, $player, '2026-11-02T18:00:00+08:00');
    $played = payAtVenue($venue, $player, '2026-11-02T11:00:00+08:00');
    $machine = new BookingStateMachine;
    $machine->transition($played, BookingStatus::CheckedIn, $owner);
    $notOver = payAtVenue($venue, $player, '2026-11-02T13:00:00+08:00');

    // 10:10 am: the hold still has time and the game hasn't ended.
    $this->travel(10)->minutes();
    $this->artisan('courtku:tick')->expectsOutput('Expired holds: 0. Completed bookings: 0. Retried webhooks: 0.');

    $this->travelTo('2026-11-02 04:00:00'); // 12:00 pm: the hold ran out, the 11 am game is over
    $this->artisan('courtku:tick')
        ->expectsOutput('Expired holds: 1. Completed bookings: 1. Retried webhooks: 0.')
        ->assertSuccessful();

    expect($late->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($played->fresh()->status)->toBe(BookingStatus::Completed)
        ->and($played->slots()->count())->toBe(2) // played time stays taken, as history
        ->and($notOver->fresh()->status)->toBe(BookingStatus::Confirmed);

    // Running it again changes nothing.
    $this->artisan('courtku:tick')->expectsOutput('Expired holds: 0. Completed bookings: 0. Retried webhooks: 0.');
});

it('ADR-022 runs the tick from a cron service only with the secret', function () {
    [, $venue] = holdsVenue();
    $booking = hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');
    $this->travel(15)->minutes();

    config(['services.cron.secret' => '']);
    $this->postJson('/api/tick', [], ['X-Cron-Secret' => ''])->assertNotFound(); // no secret set: switched off

    config(['services.cron.secret' => 'a-long-random-secret']);
    $this->postJson('/api/tick')->assertForbidden();
    $this->postJson('/api/tick', [], ['X-Cron-Secret' => 'wrong'])->assertForbidden();
    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment); // nothing ran

    $this->postJson('/api/tick', [], ['X-Cron-Secret' => 'a-long-random-secret'])
        ->assertOk()
        ->assertExactJson(['data' => ['expired_holds' => 1, 'completed_bookings' => 0, 'retried_webhooks' => 0]]);
    expect($booking->fresh()->status)->toBe(BookingStatus::Expired);
});

it('lets a payment and the expiry never both win on the same hold', function () {
    [, $venue] = holdsVenue();
    $booking = hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');
    $stale = Booking::findOrFail($booking->id); // what the expiry read a moment earlier
    $machine = new BookingStateMachine;

    // The payment arrives first and confirms the hold (M4 PR 4 does this from the gateway's callback).
    $machine->transition($booking, BookingStatus::Confirmed, null);
    $this->travel(15)->minutes();

    // The expiry, working from its earlier read, re-reads under the lock and sees "confirmed".
    expect(holdsErrorCode(fn () => $machine->transition($stale, BookingStatus::Expired, null)))->toBe('INVALID_BOOKING_STATUS')
        ->and((new Holds)->releaseExpired())->toBe(0)
        ->and($booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->slots()->count())->toBe(2);
});

it('moves a hold only along the allowed steps', function () {
    [, $venue] = holdsVenue();
    $player = User::factory()->create();
    $machine = new BookingStateMachine;

    $held = hold($venue, $player, '2026-11-02T18:00:00+08:00');
    // Not paid yet, so it can't be played, missed or paid at the counter.
    expect(holdsErrorCode(fn () => $machine->transition($held, BookingStatus::CheckedIn, null)))->toBe('INVALID_BOOKING_STATUS')
        ->and(holdsErrorCode(fn () => $machine->transition($held, BookingStatus::NoShow, null)))->toBe('INVALID_BOOKING_STATUS')
        ->and($held->canMarkPaid())->toBeFalse();

    // An expired hold stays expired: a late payment is handled on its own (M4 PR 6), never by a plain status change.
    $this->travel(15)->minutes();
    (new Holds)->releaseExpired();
    expect(holdsErrorCode(fn () => $machine->transition($held, BookingStatus::Confirmed, null)))->toBe('INVALID_BOOKING_STATUS')
        ->and(holdsErrorCode(fn () => $machine->transition($held, BookingStatus::Cancelled, null)))->toBe('INVALID_BOOKING_STATUS');
});

it('FR-24 lets the player cancel an unpaid hold at any time, freeing the time', function () {
    [, $venue] = holdsVenue(); // free cancellation closes 24 hours before; this booking is today
    $player = User::factory()->create();
    $booking = hold($venue, $player, '2026-11-02T18:00:00+08:00');

    $this->actingAs($player)->getJson("/api/v1/bookings/{$booking->code}")
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_payment')
        ->assertJsonPath('data.hold_expires_at', '2026-11-02T10:15:00+08:00')
        ->assertJsonPath('data.can_cancel', true);

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
    expect($booking->slots()->count())->toBe(0);
});

it('FR-30 shows a hold as upcoming while it lasts, and as expired once its time is up', function () {
    [, $venue] = holdsVenue();
    $player = User::factory()->create();
    $booking = hold($venue, $player, '2026-11-02T18:00:00+08:00');

    $this->actingAs($player)->getJson('/api/v1/me/bookings')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'pending_payment');

    $this->travel(15)->minutes();
    $this->actingAs($player)->getJson('/api/v1/me/bookings')->assertJsonCount(0, 'data');
    $this->actingAs($player)->getJson('/api/v1/me/bookings?when=past')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'expired');
    $this->actingAs($player)->getJson("/api/v1/bookings/{$booking->code}")
        ->assertJsonPath('data.status', 'expired')
        ->assertJsonPath('data.can_cancel', false);
});

it('FR-31 shows staff a hold waiting for payment, which they may cancel but not check in or mark paid', function () {
    [$owner, $venue] = holdsVenue();
    $booking = hold($venue, User::factory()->create(), '2026-11-02T10:30:00+08:00'); // starts within the hour

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertOk()
        ->assertJsonCount(1, 'data.bookings')
        ->assertJsonPath('data.bookings.0.status', 'pending_payment')
        ->assertJsonPath('data.bookings.0.hold_expires_at', '2026-11-02T10:15:00+08:00')
        ->assertJsonPath('data.bookings.0.actions', ['check_in' => false, 'no_show' => false, 'cancel' => true, 'mark_paid' => false]);

    // Its time is up: it disappears from the schedule, like a cancelled booking.
    $this->travel(15)->minutes();
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertJsonCount(0, 'data.bookings');
    expect($booking->fresh()->status)->toBe(BookingStatus::Expired);
});

it('FR-38 counts a hold as an upcoming booking, until its time is up', function () {
    [, $venue] = holdsVenue();
    hold($venue, User::factory()->create(), '2026-11-02T18:00:00+08:00');
    $conflicts = new BookingConflicts;

    expect($conflicts->onCourt(holdsCourt($venue)))->toHaveCount(1);

    $this->travel(15)->minutes();
    expect($conflicts->onCourt(holdsCourt($venue)))->toHaveCount(0);
});
