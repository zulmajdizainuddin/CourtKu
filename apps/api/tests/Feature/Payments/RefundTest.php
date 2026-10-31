<?php

use App\Enums\BookingSource;
use App\Enums\PaymentProblem;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateways;
use App\Services\BookingService;
use App\Services\Checkouts;
use App\Services\PaymentProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

/*
| FR-25: refunds are recorded manually by the owner. The owner refunds the player in their own Stripe
| dashboard, then records it in CourtKu. Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    $this->gateway = new FakeGateway;
    $this->app->instance(PaymentGateways::class, PaymentGateways::fake($this->gateway));
    Notification::fake();
});

/** @return array{User, Venue} the owner and a venue taking online payment, Court 1 at RM 20.00 an hour */
function refundVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create();
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    (new VenuePaymentAccount)->forceFill(['venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => "acct_refund_{$venue->id}", 'charges_enabled' => true])->save();

    return [$owner, $venue];
}

/**
 * A booking paid online (RM 20.00), optionally paying a different amount.
 *
 * @return array{Booking, Payment, User}
 */
function paidOnline(Venue $venue, ?int $paidSen = null, string $startsAt = '2026-11-03T18:00:00+08:00'): array
{
    $player = User::factory()->create();
    $service = new BookingService;
    $booking = $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online, player: $player, createdBy: $player, hold: true,
    );
    $payment = app(Checkouts::class)->start($booking->fresh());
    test()->gateway->pay($payment->checkout_id, $paidSen);
    app(PaymentProcessor::class)->applyCheckout($payment, test()->gateway->checkouts[$payment->checkout_id]);

    return [$booking->fresh(), $payment->fresh(), $player];
}

function recordRefund(User $owner, Venue $venue, Payment $payment, int $sen, string $reason = 'Refunded in Stripe')
{
    return test()->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/payments/{$payment->id}/refunds", ['amount_sen' => $sen, 'reason' => $reason]);
}

it('FR-25 lists a booking paid online and then cancelled, as money to give back', function () {
    [$owner, $venue] = refundVenue();
    [$booking, $payment, $player] = paidOnline($venue);
    paidOnline($venue, startsAt: '2026-11-03T20:00:00+08:00'); // paid and still on: not listed

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk(); // before the cutoff

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payments/attention")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $payment->id)
        ->assertJsonPath('data.0.problem', null)
        ->assertJsonPath('data.0.problem_text', 'The booking was cancelled after it was paid online.')
        ->assertJsonPath('data.0.refundable_sen', 2000);
});

it('FR-25 records a refund the owner made in Stripe: the payment leaves the list and the player sees it', function () {
    [$owner, $venue] = refundVenue();
    [$booking, $payment, $player] = paidOnline($venue, paidSen: 1500); // wrong amount: flagged

    recordRefund($owner, $venue, $payment, 1500, 'Paid the wrong amount')
        ->assertOk()
        ->assertJsonPath('data.status', 'refunded')
        ->assertJsonPath('data.refunded', 'RM 15.00')
        ->assertJsonPath('data.refundable_sen', 0);

    $refund = Refund::firstOrFail();
    expect($refund->amount_sen)->toBe(1500)
        ->and($refund->reason)->toBe('Paid the wrong amount')
        ->and($refund->recorded_by)->toBe($owner->id)
        ->and(AuditLog::where('action', 'payment.refunded')->firstOrFail()->changes)->toMatchArray(['amount_sen' => 1500]);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payments/attention")->assertJsonCount(0, 'data');
    $this->actingAs($player)->getJson("/api/v1/bookings/{$booking->code}")
        ->assertJsonPath('data.payment.status', 'refunded')
        ->assertJsonPath('data.payment.refunded', 'RM 15.00');
});

it('takes part refunds, and never more than was paid', function () {
    [$owner, $venue] = refundVenue();
    [$booking, $payment, $player] = paidOnline($venue);
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    recordRefund($owner, $venue, $payment, 1000, 'Half back, as agreed')->assertOk()->assertJsonPath('data.status', 'paid');
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payments/attention")
        ->assertJsonCount(1, 'data') // still listed: RM 10.00 left
        ->assertJsonPath('data.0.refundable_sen', 1000);

    recordRefund($owner, $venue, $payment, 1500)
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.amount_sen.0', 'You can record at most RM 10.00 (what was paid, minus earlier refunds).');
    recordRefund($owner, $venue, $payment, 1000)->assertOk()->assertJsonPath('data.status', 'refunded');
    recordRefund($owner, $venue, $payment, 100)
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.amount_sen.0', 'This payment is already fully refunded.');

    expect($payment->fresh()->refunded_sen)->toBe(2000)
        ->and(Refund::count())->toBe(2);
});

it('checks the refund form, and refuses money that was never paid', function () {
    [$owner, $venue] = refundVenue();
    [, $paid] = paidOnline($venue);
    [, $pending] = (function () use ($venue) {
        $player = User::factory()->create();
        $service = new BookingService;
        $booking = $service->store($service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse('2026-11-03T20:00:00+08:00'), 60), BookingSource::Online, player: $player, createdBy: $player, hold: true);

        return [$booking, app(Checkouts::class)->start($booking->fresh())];
    })();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/payments/{$paid->id}/refunds", [])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.amount_sen.0', 'Enter how much you refunded.')
        ->assertJsonPath('error.details.fields.reason.0', 'Say why, e.g. paid after the time ran out.');
    recordRefund($owner, $venue, $pending, 100)
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.amount_sen.0', 'Only money that was paid can be refunded.');
});

it('ADR-002 lets only the owner record refunds, and only for their own venue\'s payments', function () {
    [$owner, $venue] = refundVenue();
    [, $payment] = paidOnline($venue, paidSen: 1500);
    $staff = User::factory()->create();
    $venue->members()->attach($staff, ['role' => 'staff']);
    [$otherOwner, $otherVenue] = refundVenue();

    recordRefund($staff, $venue, $payment, 100)->assertForbidden();
    // Another owner, through their own venue's address: the payment isn't found there.
    recordRefund($otherOwner, $otherVenue, $payment, 100)->assertNotFound();

    expect(Refund::count())->toBe(0);
});

it('never turns a refunded payment back into paid when a late copy of Stripe\'s news arrives', function () {
    [$owner, $venue] = refundVenue();
    [, $payment] = paidOnline($venue, paidSen: 1500);
    recordRefund($owner, $venue, $payment, 1500)->assertOk();

    app(PaymentProcessor::class)->applyCheckout($payment->fresh(), $this->gateway->checkouts[$payment->checkout_id]);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::AmountMismatch);
});
