<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentProblem;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Notifications\PaymentNeedsAttention;
use App\Payments\CheckoutStatus;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateways;
use App\Services\BookingService;
use App\Services\Checkouts;
use App\Services\Holds;
use App\Services\PaymentProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

/*
| The safety net around online payments (plan section 9, failure cases): closing payment pages when a
| hold ends, reconciliation when Stripe's webhook never arrives, late payments, and telling the owner.
| Stripe is replaced by FakeGateway. Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    $this->gateway = new FakeGateway;
    $this->app->instance(PaymentGateways::class, PaymentGateways::fake($this->gateway));
    Notification::fake();
});

/** @return array{User, Venue} the owner and a venue taking online payment, Court 1 at RM 20.00 an hour */
function netVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create(['email' => 'owner@example.com']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'net-arena']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    (new VenuePaymentAccount)->forceFill(['venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => 'acct_net', 'charges_enabled' => true])->save();

    return [$owner, $venue];
}

/**
 * A player's hold for Monday 6:00 pm with its Stripe page open, started the way the booking page does.
 *
 * @return array{Booking, Payment, User}
 */
function netHold(Venue $venue, string $startsAt = '2026-11-02T18:00:00+08:00'): array
{
    $player = User::factory()->create();
    $service = new BookingService;
    $booking = $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online, player: $player, createdBy: $player, hold: true,
    );
    $payment = app(Checkouts::class)->start($booking->fresh());

    return [$booking, $payment, $player];
}

/** Someone else books the same time, paying at the venue. */
function netTakeTime(Venue $venue, string $startsAt = '2026-11-02T18:00:00+08:00'): Booking
{
    $other = User::factory()->create();
    $service = new BookingService;

    return $service->store($service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60), BookingSource::Online, player: $other, createdBy: $other);
}

/** The gateway's news about a payment reaches CourtKu (the webhook, or CourtKu asking). */
function netApply(Payment $payment): void
{
    app(PaymentProcessor::class)->applyCheckout($payment->fresh(), test()->gateway->checkouts[$payment->checkout_id]);
}

it('plan section 9: the player closes the tab: the hold expires, the Stripe page is closed and the time is free', function () {
    [, $venue] = netVenue();
    [$booking, $payment] = netHold($venue);

    $this->travel(15)->minutes();
    $this->artisan('courtku:tick')->assertSuccessful();

    expect($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($booking->slots()->count())->toBe(0)
        ->and($this->gateway->checkouts[$payment->checkout_id]->status)->toBe(CheckoutStatus::Expired) // nobody can pay it any more
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Expired);
});

it('confirms instead of expiring when Stripe says the player paid just before the hold ran out', function () {
    [, $venue] = netVenue();
    [$booking, $payment] = netHold($venue);
    $this->gateway->pay($payment->checkout_id); // paid at 10:14, but the webhook hasn't arrived yet

    $this->travel(15)->minutes();
    $this->artisan('courtku:tick')->assertSuccessful();

    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Paid);
});

it('closes the Stripe page when a booking waiting for payment is cancelled, by the player or by staff', function () {
    [$owner, $venue] = netVenue();
    [$first, $firstPayment, $player] = netHold($venue);
    [$second, $secondPayment] = netHold($venue, '2026-11-02T20:00:00+08:00');

    $this->actingAs($player)->postJson("/api/v1/bookings/{$first->code}/cancel")->assertOk();
    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/bookings/{$second->code}/cancel", ['reason' => 'Rain'])->assertOk();

    expect($this->gateway->checkouts[$firstPayment->checkout_id]->status)->toBe(CheckoutStatus::Expired)
        ->and($this->gateway->checkouts[$secondPayment->checkout_id]->status)->toBe(CheckoutStatus::Expired)
        ->and($firstPayment->fresh()->status)->toBe(PaymentStatus::Expired);
});

it('flags for a refund, and tells the owner and the player, when a cancelled booking turns out to be paid', function () {
    [$owner, $venue] = netVenue();
    [$booking, $payment, $player] = netHold($venue);
    $this->gateway->pay($payment->checkout_id); // paid while the player pressed Cancel

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    expect($booking->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::BookingCancelled);
    Notification::assertSentTo($owner, PaymentNeedsAttention::class);
    Notification::assertSentTo($player, PaymentNeedsAttention::class);
});

it('plan section 9: the webhook never arrives: reconciliation settles it after 20 minutes', function () {
    [, $venue] = netVenue();
    $venue->forceFill(['hold_minutes' => 30])->save();
    [$booking, $payment] = netHold($venue);
    $this->gateway->pay($payment->checkout_id); // paid, but Stripe's message got lost

    $this->travel(19)->minutes();
    $this->artisan('courtku:tick')->expectsOutputToContain('Reconciled payments: 0.');
    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment);

    $this->travel(1)->minutes();
    $this->artisan('courtku:tick')->expectsOutputToContain('Reconciled payments: 1.');
    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
});

it('asks Stripe about each pending payment at most every 15 minutes', function () {
    [, $venue] = netVenue();
    [, $payment] = netHold($venue);
    $this->gateway->down = true; // Stripe can't answer, so it stays pending
    $this->travel(20)->minutes();

    $this->artisan('courtku:tick'); // asks (and fails)
    $this->artisan('courtku:tick'); // a minute later: too soon to ask again
    $this->gateway->down = false;
    $this->travel(15)->minutes();
    app(Checkouts::class)->reconcile();

    expect($payment->fresh()->checked_at->toDateTimeString())->toBe('2026-11-02 02:35:00')
        ->and($this->gateway->lookups)->toBe(1); // the failed tries don't count as lookups; one real one
});

it('plan section 7: a late payment takes the time back when nobody has booked it meanwhile', function () {
    [$owner, $venue] = netVenue();
    [$booking, $payment, $player] = netHold($venue);
    $this->gateway->down = true; // the page couldn't be closed when the hold ran out
    $this->travel(15)->minutes();
    (new Holds)->releaseExpired();
    $this->gateway->down = false;
    $this->gateway->pay($payment->checkout_id); // the player pays a minute later

    netApply($payment);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->paid_at)->not->toBeNull()
        ->and($booking->slots()->count())->toBe(2) // its half hours are back
        ->and($payment->fresh()->problem)->toBeNull()
        ->and(AuditLog::where('action', 'booking.reclaimed')->exists())->toBeTrue();
    Notification::assertNothingSent();
});

it('plan section 7: a late payment is flagged for a refund, and the owner and player told, when the time was taken', function () {
    [$owner, $venue] = netVenue();
    [$booking, $payment, $player] = netHold($venue);
    $this->gateway->down = true;
    $this->travel(15)->minutes();
    (new Holds)->releaseExpired();
    $this->gateway->down = false;
    $taken = netTakeTime($venue); // someone else books the time
    $this->gateway->pay($payment->checkout_id);

    netApply($payment);

    expect($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($taken->fresh()->status)->toBe(BookingStatus::Confirmed) // the other player keeps it
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::BookingExpired);
    Notification::assertSentTo($owner, PaymentNeedsAttention::class, fn ($n) => $n->toMail($owner)->subject === "A payment at {$venue->name} needs your attention");
    Notification::assertSentTo($player, PaymentNeedsAttention::class, fn ($n) => str_contains(implode(' ', $n->toMail($player)->introLines), 'will refund you'));
});

it('never takes back time that has already started', function () {
    [, $venue] = netVenue();
    [$booking, $payment] = netHold($venue, '2026-11-02T10:30:00+08:00');
    $this->gateway->down = true;
    $this->travel(31)->minutes(); // 10:31: the game has started
    (new Holds)->releaseExpired();
    $this->gateway->down = false;
    $this->gateway->pay($payment->checkout_id);

    netApply($payment);

    expect($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::BookingExpired);
});

it('plan section 9: a wrong amount is flagged, the booking not confirmed, and the owner alerted', function () {
    [$owner, $venue] = netVenue();
    [$booking, $payment] = netHold($venue);
    $this->gateway->pay($payment->checkout_id, 1500);

    netApply($payment);

    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment)
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::AmountMismatch);
    Notification::assertSentTo($owner, PaymentNeedsAttention::class, fn ($n) => str_contains(implode(' ', $n->toMail($owner)->introLines), 'RM 15.00'));
});

it('FR-25 lists the payments needing the owner\'s attention, owners only', function () {
    [$owner, $venue] = netVenue();
    [$booking, $payment, $player] = netHold($venue);
    $this->gateway->pay($payment->checkout_id, 1500);
    netApply($payment);
    netHold($venue, '2026-11-02T20:00:00+08:00'); // a normal pending payment: not listed
    $staff = User::factory()->create();
    $venue->members()->attach($staff, ['role' => 'staff']);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payments/attention")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.problem', 'amount_mismatch')
        ->assertJsonPath('data.0.problem_text', 'The amount paid didn\'t match the booking\'s total, so the booking wasn\'t confirmed.')
        ->assertJsonPath('data.0.paid_amount', 'RM 15.00')
        ->assertJsonPath('data.0.booking.code', $booking->code)
        ->assertJsonPath('data.0.booking.customer.email', $player->email);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/payments/attention")->assertForbidden();
});
