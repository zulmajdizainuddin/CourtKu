<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProblem;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Models\WebhookEvent;
use App\Payments\CheckoutRequest;
use App\Payments\FakeGateway;
use App\Payments\PaymentEventType;
use App\Payments\PaymentGateways;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Services\Holds;
use App\Services\PaymentProcessor;
use Carbon\CarbonImmutable;

/*
| The payment webhook (plan section 9, ADR-006): only a correctly signed message from the gateway
| confirms a payment. Stripe is replaced by FakeGateway, which signs its webhooks the same way in spirit
| (HMAC-SHA256 with a shared secret). Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

const WEBHOOK_SECRET = 'whsec_courtku_test';

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    $this->gateway = new FakeGateway;
    $this->app->instance(PaymentGateways::class, PaymentGateways::fake($this->gateway));
    config(['services.stripe.webhook_secret' => WEBHOOK_SECRET]);
});

/** A venue whose own Stripe account is connected and ready, open every day 08:00–23:00, Court 1 at RM 20.00 an hour. */
function webhookVenue(string $accountId = 'acct_venue'): Venue
{
    $venue = Venue::factory()->active()->create();
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    (new VenuePaymentAccount)->forceFill([
        'venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => $accountId, 'charges_enabled' => true,
    ])->save();

    return $venue;
}

/**
 * A player's hold for Monday 6:00 pm (RM 20.00) with a payment page open on the venue's account,
 * the way M4 PR 5 starts a payment.
 *
 * @return array{Booking, Payment}
 */
function heldAndPaying(Venue $venue, string $startsAt = '2026-11-02T18:00:00+08:00'): array
{
    $player = User::factory()->create();
    $service = new BookingService;
    $booking = $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online, player: $player, createdBy: $player, hold: true,
    );
    $account = $venue->paymentAccount;
    $checkout = test()->gateway->createCheckout($account->account_id, new CheckoutRequest(
        $booking->code, $booking->total_sen, "Court booking {$booking->code}", $player->email,
        "https://courtku.vercel.app/bookings/{$booking->code}/status", CarbonImmutable::now()->addMinutes(15), "payment-{$booking->id}",
    ));
    $payment = new Payment;
    $payment->forceFill([
        'booking_id' => $booking->id, 'venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => $account->account_id,
        'checkout_id' => $checkout->id, 'amount_sen' => $booking->total_sen, 'status' => PaymentStatus::Pending,
    ])->save();

    return [$booking, $payment];
}

/** Sends a webhook to CourtKu exactly as the gateway would: the raw body and its signature header. */
function sendWebhook(string $payload, array $headers)
{
    $server = collect($headers)->mapWithKeys(fn ($value, $name) => ['HTTP_'.strtoupper(str_replace('-', '_', $name)) => $value])->all();

    return test()->call('POST', '/api/v1/webhooks/payments/stripe', [], [], [], [...$server, 'CONTENT_TYPE' => 'application/json'], $payload);
}

/** The player pays (optionally a different amount) and the gateway sends its signed "paid" webhook. */
function payAndNotify(Payment $payment, ?int $amountSen = null, string $secret = WEBHOOK_SECRET, ?string $eventId = null)
{
    test()->gateway->pay($payment->checkout_id, $amountSen);

    return sendWebhook(...test()->gateway->webhook($payment->checkout_id, PaymentEventType::Paid, $secret, $eventId));
}

it('FR-17 confirms the booking when the signed webhook says it was paid', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());

    payAndNotify($payment)->assertOk()->assertExactJson(['received' => true]);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->paid_at)->not->toBeNull()
        ->and($booking->payment_method)->toBe(PaymentMethod::Online)
        ->and($booking->slots()->count())->toBe(2) // the time stays the player's
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($payment->fresh()->paid_amount_sen)->toBe(2000)
        ->and($payment->fresh()->problem)->toBeNull()
        ->and(WebhookEvent::firstOrFail()->processed_at)->not->toBeNull()
        ->and(AuditLog::where('action', 'booking.confirmed')->firstOrFail()->changes)->toMatchArray(['from' => 'pending_payment', 'reason' => 'Paid online'])
        ->and(AuditLog::where('action', 'booking.paid')->firstOrFail()->user_id)->toBeNull(); // the payment did it, not staff
});

it('plan section 9: a webhook sent twice, or again after the booking is confirmed, changes nothing', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());
    $this->gateway->pay($payment->checkout_id);
    [$payload, $headers] = $this->gateway->webhook($payment->checkout_id, PaymentEventType::Paid, WEBHOOK_SECRET);

    sendWebhook($payload, $headers)->assertOk();
    $paidAt = $booking->fresh()->paid_at;
    $this->travel(5)->minutes();
    sendWebhook($payload, $headers)->assertOk(); // Stripe retries, or sends the same news twice

    expect(WebhookEvent::count())->toBe(1)
        ->and(AuditLog::where('action', 'booking.confirmed')->count())->toBe(1)
        ->and($booking->fresh()->paid_at->equalTo($paidAt))->toBeTrue();
});

it('ADR-006 rejects a webhook that isn\'t signed with CourtKu\'s webhook secret: 401, logged, nothing changes', function (Closure $send) {
    [$booking, $payment] = heldAndPaying(webhookVenue());
    $this->gateway->pay($payment->checkout_id);

    $send($payment)->assertStatus(401)->assertJsonPath('error.code', 'INVALID_SIGNATURE');

    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and(WebhookEvent::count())->toBe(0);
})->with([
    'signed with another secret' => [fn (Payment $p) => sendWebhook(...test()->gateway->webhook($p->checkout_id, PaymentEventType::Paid, 'whsec_someone_else'))],
    'no signature' => [fn (Payment $p) => sendWebhook(test()->gateway->webhook($p->checkout_id, PaymentEventType::Paid, WEBHOOK_SECRET)[0], [])],
    'body changed after signing' => [function (Payment $p) {
        [$payload, $headers] = test()->gateway->webhook($p->checkout_id, PaymentEventType::Paid, WEBHOOK_SECRET);

        return sendWebhook(str_replace('paid', 'pay', $payload), $headers);
    }],
    'CourtKu has no webhook secret set' => [function (Payment $p) {
        config(['services.stripe.webhook_secret' => '']);

        return sendWebhook(...test()->gateway->webhook($p->checkout_id, PaymentEventType::Paid, ''));
    }],
]);

it('plan section 9: confirms only when the amount paid equals what the booking costs', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());

    payAndNotify($payment, amountSen: 1000)->assertOk(); // RM 10.00 paid for a RM 20.00 booking

    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment) // not confirmed
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Paid) // but the money did come in...
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::AmountMismatch) // ...so the owner sorts it out
        ->and($payment->fresh()->paid_amount_sen)->toBe(1000)
        ->and(AuditLog::where('action', 'payment.problem')->firstOrFail()->changes)->toMatchArray(['problem' => 'amount_mismatch', 'paid_sen' => 1000, 'expected_sen' => 2000]);
});

it('flags a late payment for a refund when someone else has booked the time meanwhile', function () {
    $venue = webhookVenue();
    [$booking, $payment] = heldAndPaying($venue);
    $this->travel(15)->minutes();
    (new Holds)->releaseExpired(); // the time went back to other players...
    heldAndPaying($venue); // ...and another player took it

    payAndNotify($payment)->assertOk(); // the first player's payment arrives anyway

    expect($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($payment->fresh()->problem)->toBe(PaymentProblem::BookingExpired);
});

it('confirms a payment that arrives just after the hold time, while the time is still held', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());
    $this->travel(16)->minutes(); // nobody has looked at this court since, so the hold hasn't been released

    payAndNotify($payment)->assertOk();

    // The player paid and still has the time: confirming is fair to everyone (the row lock decides who wins).
    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
});

it('flags a payment for a booking cancelled meanwhile, and a second payment for one already paid', function () {
    $venue = webhookVenue();
    [$cancelled, $first] = heldAndPaying($venue);
    (new BookingStateMachine)->transition($cancelled, BookingStatus::Cancelled, null, 'Cancelled by the player');
    payAndNotify($first)->assertOk();

    [$booking, $payment] = heldAndPaying($venue, '2026-11-02T20:00:00+08:00');
    payAndNotify($payment)->assertOk();
    // A second payment page for the same booking (e.g. opened in another tab), paid too.
    $page = $this->gateway->createCheckout('acct_venue', new CheckoutRequest(
        $booking->code, 2000, 'Court booking', 'player@example.com', 'https://courtku.vercel.app/', CarbonImmutable::now()->addMinutes(15), 'second-tab',
    ));
    $second = new Payment;
    $second->forceFill([...$payment->only(['booking_id', 'venue_id', 'gateway', 'account_id', 'amount_sen']), 'checkout_id' => $page->id, 'status' => PaymentStatus::Pending])->save();
    payAndNotify($second)->assertOk();

    expect($cancelled->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($first->fresh()->problem)->toBe(PaymentProblem::BookingCancelled)
        ->and($booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($second->fresh()->problem)->toBe(PaymentProblem::AlreadyPaid);
});

it('plan section 9: a failed payment leaves the hold, so the player can try again while it lasts', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());

    sendWebhook(...$this->gateway->webhook($payment->checkout_id, PaymentEventType::Failed, WEBHOOK_SECRET))->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed)
        ->and($booking->fresh()->status)->toBe(BookingStatus::PendingPayment)
        ->and($booking->slots()->count())->toBe(2);
});

it('marks a payment page that closed unpaid, and never un-pays a paid one', function () {
    $venue = webhookVenue();
    [, $unpaid] = heldAndPaying($venue);
    [, $paid] = heldAndPaying($venue, '2026-11-02T20:00:00+08:00');
    payAndNotify($paid)->assertOk();

    sendWebhook(...$this->gateway->webhook($unpaid->checkout_id, PaymentEventType::Expired, WEBHOOK_SECRET))->assertOk();
    sendWebhook(...$this->gateway->webhook($paid->checkout_id, PaymentEventType::Expired, WEBHOOK_SECRET))->assertOk();

    expect($unpaid->fresh()->status)->toBe(PaymentStatus::Expired)
        ->and($paid->fresh()->status)->toBe(PaymentStatus::Paid);
});

it('ADR-024 ignores news about a payment from another venue\'s Stripe account', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());
    $this->gateway->pay($payment->checkout_id);
    $this->gateway->checkoutAccounts[$payment->checkout_id] = 'acct_someone_else'; // signed by Stripe, but about another account

    sendWebhook(...$this->gateway->webhook($payment->checkout_id, PaymentEventType::Paid, WEBHOOK_SECRET))->assertOk();

    expect($booking->fresh()->status)->toBe(BookingStatus::PendingPayment)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('answers 200 to events it doesn\'t act on, so Stripe stops sending them', function () {
    webhookVenue();
    $payload = (string) json_encode(['id' => 'evt_other', 'type' => 'customer.created', 'checkout' => 'cs_unknown']);

    sendWebhook($payload, ['X-Fake-Signature' => hash_hmac('sha256', $payload, WEBHOOK_SECRET)])->assertOk();

    expect(WebhookEvent::count())->toBe(0);
});

it('ADR-022 finishes a webhook that was saved but not acted on, at the next tick', function () {
    [$booking, $payment] = heldAndPaying(webhookVenue());
    $this->gateway->pay($payment->checkout_id);
    [$payload, $headers] = $this->gateway->webhook($payment->checkout_id, PaymentEventType::Paid, WEBHOOK_SECRET);
    // The request saved the event, then failed before acting on it.
    $event = $this->gateway->parseWebhook($payload, $headers['X-Fake-Signature'], WEBHOOK_SECRET);
    app(PaymentProcessor::class)->save('stripe', $event);

    $this->travel(2)->minutes();
    $this->artisan('courtku:tick')->expectsOutput('Expired holds: 0. Completed bookings: 0. Retried webhooks: 1. Reconciled payments: 0.');

    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and(WebhookEvent::firstOrFail()->processed_at)->not->toBeNull();
});

it('FR-28 never lets staff mark a booking "paid online" by hand', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = webhookVenue();
    $venue->members()->attach($owner, ['role' => 'owner']);
    $player = User::factory()->create();
    $service = new BookingService;
    $booking = $service->store($service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse('2026-11-02T18:00:00+08:00'), 60), BookingSource::Online, player: $player, createdBy: $player);

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/bookings/{$booking->code}/paid", ['method' => 'online'])
        ->assertStatus(422);
    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/bookings/{$booking->code}/paid", ['method' => 'cash'])
        ->assertOk();
});
