<?php

use App\Payments\CheckoutRequest;
use App\Payments\CheckoutStatus;
use App\Payments\InvalidWebhookSignature;
use App\Payments\PaymentEventType;
use App\Payments\PaymentGatewayException;
use App\Payments\StripeGateway;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/*
| StripeGateway against Stripe's API shapes (ADR-023), with no internet: Http::fake answers as
| Stripe would. Now is 1 Nov 2026, 10:26:40 am in Malaysia (Unix time 1793500000).
*/

beforeEach(function () {
    $this->travelTo(CarbonImmutable::createFromTimestamp(1793500000));
    Http::preventStrayRequests();
});

/** A Stripe Checkout Session as Stripe's API returns it. */
function stripeSession(array $changes = []): array
{
    return [
        'id' => 'cs_test_a1',
        'object' => 'checkout.session',
        'url' => 'https://checkout.stripe.com/c/pay/cs_test_a1',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'amount_total' => 4000,
        'currency' => 'myr',
        'client_reference_id' => 'CK-7F3K2',
        'expires_at' => 1793501860,
        ...$changes,
    ];
}

function stripeRequest(): CheckoutRequest
{
    return new CheckoutRequest(
        bookingCode: 'CK-7F3K2',
        amountSen: 4000,
        description: 'Court booking CK-7F3K2: Court 1, Fri 6 Nov, 6:00 pm',
        customerEmail: 'siti@example.com',
        returnUrl: 'https://courtku.vercel.app/bookings/CK-7F3K2/status',
        expiresAt: CarbonImmutable::now()->addMinutes(15),
        idempotencyKey: 'payment-1',
    );
}

/** A Stripe-Signature header for this body, signed with this secret at this time. */
function stripeSignature(string $payload, string $secret = 'whsec_test_secret', ?int $time = null): string
{
    $time ??= CarbonImmutable::now()->getTimestamp();

    return "t={$time},v1=".hash_hmac('sha256', "{$time}.{$payload}", $secret);
}

function stripeEvent(string $type, array $session = []): string
{
    return (string) json_encode(['id' => 'evt_test_1', 'type' => $type, 'account' => 'acct_venue', 'data' => ['object' => stripeSession($session)]]);
}

it('ADR-023 opens a Stripe payment page in ringgit, for the booking\'s amount in sen, on the venue\'s own account', function () {
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(stripeSession())]);

    $checkout = (new StripeGateway('sk_test_123'))->createCheckout('acct_venue', stripeRequest());

    expect($checkout->id)->toBe('cs_test_a1')
        ->and($checkout->url)->toBe('https://checkout.stripe.com/c/pay/cs_test_a1')
        ->and($checkout->status)->toBe(CheckoutStatus::Open)
        ->and($checkout->paid)->toBeFalse()
        ->and($checkout->amountSen)->toBe(4000)
        ->and($checkout->bookingCode)->toBe('CK-7F3K2');

    Http::assertSent(function (Request $request) {
        $data = $request->data();

        return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer sk_test_123') // CourtKu's own platform key
            && $request->hasHeader('Stripe-Account', 'acct_venue') // ... acting for the venue: the money lands there (ADR-024)
            && $request->hasHeader('Idempotency-Key', 'payment-1') // a retry never opens a second page
            && $data['mode'] === 'payment'
            && $data['line_items'][0]['price_data']['currency'] === 'myr'
            && (int) $data['line_items'][0]['price_data']['unit_amount'] === 4000 // sen, like CourtKu
            && (int) $data['line_items'][0]['quantity'] === 1
            && $data['client_reference_id'] === 'CK-7F3K2'
            && $data['metadata']['booking_code'] === 'CK-7F3K2'
            && $data['success_url'] === 'https://courtku.vercel.app/bookings/CK-7F3K2/status'
            && ! isset($data['payment_method_types']); // Stripe shows what the venue's account allows (FPX once activated)
    });
});

it('keeps the payment page open for Stripe\'s minimum, since CourtKu closes it itself when the hold runs out', function () {
    Http::fake(['api.stripe.com/*' => Http::response(stripeSession())]);

    (new StripeGateway('sk_test_123'))->createCheckout(null, stripeRequest()); // the hold ends in 15 minutes

    // Stripe refuses less than 30 minutes, so it asks for 31.
    Http::assertSent(fn (Request $request) => (int) $request->data()['expires_at'] === 1793500000 + 31 * 60);
});

it('reads how a payment page stands: open, paid or expired', function (array $session, CheckoutStatus $status, bool $paid, bool $hasUrl) {
    Http::fake(['api.stripe.com/v1/checkout/sessions/cs_test_a1' => Http::response(stripeSession($session))]);

    $checkout = (new StripeGateway('sk_test_123'))->findCheckout('acct_venue', 'cs_test_a1');

    expect($checkout->status)->toBe($status)
        ->and($checkout->paid)->toBe($paid)
        ->and($checkout->url !== null)->toBe($hasUrl)
        ->and($checkout->expiresAt?->getTimestamp())->toBe(1793501860);
})->with([
    'still open' => [[], CheckoutStatus::Open, false, true],
    'paid' => [['status' => 'complete', 'payment_status' => 'paid', 'url' => null], CheckoutStatus::Complete, true, false],
    'finished, money not in yet' => [['status' => 'complete', 'payment_status' => 'unpaid', 'url' => null], CheckoutStatus::Complete, false, false],
    'expired' => [['status' => 'expired', 'url' => null], CheckoutStatus::Expired, false, false],
]);

it('closes an open payment page, and leaves a paid one paid', function () {
    Http::fake([
        'api.stripe.com/v1/checkout/sessions/cs_test_open/expire' => Http::response(stripeSession(['id' => 'cs_test_open', 'status' => 'expired', 'url' => null])),
        // Stripe answers 400 when the page is no longer open; the gateway then looks it up.
        'api.stripe.com/v1/checkout/sessions/cs_test_paid/expire' => Http::response(['error' => ['message' => 'Only Checkout Sessions with a status in ["open"] can be expired.']], 400),
        'api.stripe.com/v1/checkout/sessions/cs_test_paid' => Http::response(stripeSession(['id' => 'cs_test_paid', 'status' => 'complete', 'payment_status' => 'paid', 'url' => null])),
    ]);
    $stripe = new StripeGateway('sk_test_123');

    expect($stripe->expireCheckout('acct_venue', 'cs_test_open')->status)->toBe(CheckoutStatus::Expired);

    $paid = $stripe->expireCheckout('acct_venue', 'cs_test_paid');
    expect($paid->status)->toBe(CheckoutStatus::Complete)
        ->and($paid->paid)->toBeTrue();
});

it('turns Stripe errors and network failures into 502 PAYMENT_GATEWAY_ERROR with a clear message', function (Closure $answer, string $message) {
    Http::fake(['api.stripe.com/*' => $answer]);

    try {
        (new StripeGateway('sk_test_wrong'))->findCheckout('acct_venue', 'cs_test_a1');
        $this->fail('Expected a PaymentGatewayException.');
    } catch (PaymentGatewayException $e) {
        expect($e->errorCode)->toBe('PAYMENT_GATEWAY_ERROR')
            ->and($e->status)->toBe(502)
            ->and($e->getMessage())->toBe($message);
    }
})->with([
    'wrong platform key' => [fn () => Http::response(['error' => ['message' => 'Invalid API Key provided']], 401), 'Online payments are not set up correctly on CourtKu. Please try again later.'],
    'Stripe down' => [fn () => Http::response([], 500), 'The payment service didn\'t answer. Please try again in a minute.'],
    'no connection' => [fn () => throw new ConnectionException('timed out'), 'The payment service didn\'t answer. Please try again in a minute.'],
]);

it('ADR-006 verifies a webhook signed exactly as Stripe documents it', function () {
    // The signature below was worked out separately (Python's hmac module), not by the code under test:
    // HMAC-SHA256 of "1793500000.<body>" with the secret "whsec_test_secret".
    $payload = '{"id":"evt_test_1","type":"checkout.session.completed","data":{"object":{"id":"cs_test_a1","url":null,"status":"complete","payment_status":"paid","amount_total":4000,"currency":"myr","client_reference_id":"CK-7F3K2","expires_at":1793501860}}}';
    $header = 't=1793500000,v1=b88088229de1e618ff44857d281ce8ee0da2d9a96f801ef243843830bf7d1e76';

    $event = (new StripeGateway('sk_test_123'))->parseWebhook($payload, $header, 'whsec_test_secret');

    expect($event->id)->toBe('evt_test_1')
        ->and($event->type)->toBe(PaymentEventType::Paid)
        ->and($event->checkout->id)->toBe('cs_test_a1')
        ->and($event->checkout->paid)->toBeTrue()
        ->and($event->checkout->amountSen)->toBe(4000)
        ->and($event->checkout->currency)->toBe('myr')
        ->and($event->checkout->bookingCode)->toBe('CK-7F3K2');
});

it('ADR-006 rejects a webhook that wasn\'t signed by Stripe with this venue\'s secret', function (Closure $makeHeader) {
    $payload = stripeEvent('checkout.session.completed', ['status' => 'complete', 'payment_status' => 'paid']);

    expect(fn () => (new StripeGateway('sk_test_123'))->parseWebhook($payload, $makeHeader($payload), 'whsec_test_secret'))
        ->toThrow(InvalidWebhookSignature::class);
})->with([
    'no signature' => [fn (string $payload) => ''],
    'another secret' => [fn (string $payload) => stripeSignature($payload, 'whsec_someone_else')],
    'body changed after signing' => [fn (string $payload) => stripeSignature(str_replace('4000', '1', $payload))],
    'signed 6 minutes ago (replayed)' => [fn (string $payload) => stripeSignature($payload, time: 1793500000 - 360)],
    'signature without a time' => [fn (string $payload) => 'v1='.hash_hmac('sha256', $payload, 'whsec_test_secret')],
]);

it('accepts a webhook when any of its signatures matches, as when Stripe rolls the secret', function () {
    $payload = stripeEvent('checkout.session.completed', ['status' => 'complete', 'payment_status' => 'paid']);
    $header = stripeSignature($payload, 'whsec_old_secret').',v1='.hash_hmac('sha256', "1793500000.{$payload}", 'whsec_test_secret');

    $event = (new StripeGateway('sk_test_123'))->parseWebhook($payload, $header, 'whsec_test_secret');

    expect($event?->type)->toBe(PaymentEventType::Paid)
        ->and($event?->accountId)->toBe('acct_venue'); // which venue's account it happened on
});

it('reads only the webhook events CourtKu acts on', function (string $type, array $session, ?PaymentEventType $expected) {
    $payload = stripeEvent($type, $session);

    expect((new StripeGateway('sk_test_123'))->parseWebhook($payload, stripeSignature($payload), 'whsec_test_secret')?->type)->toBe($expected);
})->with([
    'paid by card' => ['checkout.session.completed', ['status' => 'complete', 'payment_status' => 'paid'], PaymentEventType::Paid],
    'finished, money comes later' => ['checkout.session.completed', ['status' => 'complete', 'payment_status' => 'unpaid'], null],
    'money came later' => ['checkout.session.async_payment_succeeded', ['status' => 'complete', 'payment_status' => 'paid'], PaymentEventType::Paid],
    'money never came' => ['checkout.session.async_payment_failed', ['status' => 'complete'], PaymentEventType::Failed],
    'page expired' => ['checkout.session.expired', ['status' => 'expired'], PaymentEventType::Expired],
    'something else' => ['customer.created', [], null],
]);

it('ADR-024 opens a Stripe account for a venue that is the venue\'s own: its dashboard, its fees, its losses', function () {
    Http::fake(['api.stripe.com/v2/core/accounts' => Http::response(['id' => 'acct_1Venue', 'object' => 'v2.core.account'])]);

    $id = (new StripeGateway('sk_test_123'))->createAccount('owner@example.com', 'Arena Demo', 'courtku-venue-9-account');

    expect($id)->toBe('acct_1Venue');
    Http::assertSent(function (Request $request) {
        $data = $request->data();

        return $request->url() === 'https://api.stripe.com/v2/core/accounts'
            && $request->hasHeader('Stripe-Version') // Accounts v2 needs a pinned version
            && $request->hasHeader('Idempotency-Key', 'courtku-venue-9-account') // a double tap never makes two
            && ! $request->hasHeader('Stripe-Account')
            && $data['identity']['country'] === 'MY'
            && $data['contact_email'] === 'owner@example.com'
            && $data['display_name'] === 'Arena Demo'
            && $data['dashboard'] === 'full'
            && $data['defaults']['responsibilities'] === ['fees_collector' => 'stripe', 'losses_collector' => 'stripe']
            && $data['configuration']['merchant']['capabilities']['card_payments']['requested'] === true;
    });
});

it('ADR-024 makes a one-time link to Stripe\'s own sign-up pages', function () {
    Http::fake(['api.stripe.com/v2/core/account_links' => Http::response(['object' => 'v2.core.account_link', 'url' => 'https://connect.stripe.com/d/setup/abc'])]);

    $url = (new StripeGateway('sk_test_123'))->onboardingUrl('acct_1Venue', 'https://courtku.vercel.app/back', 'https://courtku.vercel.app/again');

    expect($url)->toBe('https://connect.stripe.com/d/setup/abc');
    Http::assertSent(fn (Request $request) => $request['account'] === 'acct_1Venue'
        && $request['use_case']['type'] === 'account_onboarding'
        && $request['use_case']['account_onboarding']['return_url'] === 'https://courtku.vercel.app/back'
        && $request['use_case']['account_onboarding']['refresh_url'] === 'https://courtku.vercel.app/again');
});

it('reads whether a venue\'s account can take payments yet', function (string $status, bool $ready) {
    Http::fake(['api.stripe.com/v2/core/accounts/acct_1Venue*' => Http::response([
        'id' => 'acct_1Venue',
        'display_name' => 'Arena Demo',
        'identity' => ['country' => 'MY'],
        'configuration' => ['merchant' => ['capabilities' => ['card_payments' => ['status' => $status]]]],
    ])]);

    $account = (new StripeGateway('sk_test_123'))->account('acct_1Venue');

    expect($account->name)->toBe('Arena Demo')
        ->and($account->country)->toBe('MY')
        ->and($account->chargesEnabled)->toBe($ready);
})->with([
    'sign-up not finished' => ['restricted', false],
    'Stripe still checking' => ['pending', false],
    'ready' => ['active', true],
]);
