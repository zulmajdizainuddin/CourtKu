<?php

use App\Payments\CheckoutRequest;
use App\Payments\CheckoutStatus;
use App\Payments\FakeGateway;
use App\Payments\InvalidWebhookSignature;
use App\Payments\PaymentEventType;
use App\Payments\PaymentGatewayException;
use Carbon\CarbonImmutable;

/*
| The pretend gateway the payment tests use. It must behave like the real one where it matters:
| one page per idempotency key, paid pages stay paid, and only correctly signed webhooks count.
*/

function fakeRequest(string $key = 'payment-1'): CheckoutRequest
{
    return new CheckoutRequest('CK-7F3K2', 4000, 'Court booking CK-7F3K2', 'siti@example.com', 'http://localhost:3000/bookings/CK-7F3K2/status', CarbonImmutable::now()->addMinutes(15), $key);
}

it('opens one payment page per idempotency key, and lets a test pay it', function () {
    $fake = new FakeGateway;

    $first = $fake->createCheckout('acct_venue', fakeRequest());
    expect($fake->createCheckout('acct_venue', fakeRequest())->id)->toBe($first->id) // a retry gets the same page
        ->and($fake->createCheckout('acct_venue', fakeRequest('payment-2'))->id)->not->toBe($first->id);

    $fake->pay($first->id);
    expect($fake->findCheckout('acct_venue', $first->id)->paid)->toBeTrue()
        ->and($fake->expireCheckout('acct_venue', $first->id)->status)->toBe(CheckoutStatus::Complete) // paid stays paid
        ->and(fn () => $fake->findCheckout('acct_other_venue', $first->id))->toThrow(PaymentGatewayException::class); // only on its own account
});

it('signs its webhooks, and refuses ones signed with another secret', function () {
    $fake = new FakeGateway;
    $checkout = $fake->createCheckout('acct_venue', fakeRequest());
    $fake->pay($checkout->id, 3000); // the player paid a different amount

    [$payload, $headers] = $fake->webhook($checkout->id, PaymentEventType::Paid, 'whsec_venue');
    $event = $fake->parseWebhook($payload, $headers['X-Fake-Signature'], 'whsec_venue');

    expect($event?->type)->toBe(PaymentEventType::Paid)
        ->and($event?->accountId)->toBe('acct_venue')
        ->and($event?->checkout->amountSen)->toBe(3000)
        ->and(fn () => $fake->parseWebhook($payload, $headers['X-Fake-Signature'], 'whsec_other'))->toThrow(InvalidWebhookSignature::class);
});

it('can pretend the gateway is down', function () {
    $fake = new FakeGateway;
    $fake->down = true;

    expect(fn () => $fake->createCheckout('acct_venue', fakeRequest()))->toThrow(PaymentGatewayException::class);
});
