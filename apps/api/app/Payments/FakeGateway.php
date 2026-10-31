<?php

namespace App\Payments;

use Carbon\CarbonImmutable;

/**
 * A pretend gateway for tests: keeps payment pages in memory and lets a test "pay" them, so the
 * payment flow can be tested without the internet or a Stripe account.
 *
 * Its webhooks are signed too (HMAC-SHA256 of the body with the secret, header X-Fake-Signature),
 * so tests also prove that unsigned or wrongly signed webhooks change nothing.
 */
class FakeGateway implements PaymentGateway
{
    /** @var array<string, Checkout> every payment page, by id */
    public array $checkouts = [];

    /** @var list<CheckoutRequest> what CourtKu asked for, in order */
    public array $requests = [];

    /** @var array<string, string> payment page id by idempotency key */
    private array $idempotencyKeys = [];

    /** true = act as if the gateway can't be reached */
    public bool $down = false;

    public function createCheckout(CheckoutRequest $request): Checkout
    {
        $this->failIfDown();
        $this->requests[] = $request;

        // The same key twice = the same page, like Stripe's Idempotency-Key.
        if (isset($this->idempotencyKeys[$request->idempotencyKey])) {
            return $this->checkouts[$this->idempotencyKeys[$request->idempotencyKey]];
        }

        $id = 'cs_fake_'.(count($this->checkouts) + 1);
        $this->idempotencyKeys[$request->idempotencyKey] = $id;

        return $this->checkouts[$id] = new Checkout(
            id: $id,
            url: "https://pay.example.test/{$id}",
            status: CheckoutStatus::Open,
            paid: false,
            amountSen: $request->amountSen,
            currency: 'myr',
            bookingCode: $request->bookingCode,
            expiresAt: $request->expiresAt,
        );
    }

    public function findCheckout(string $id): Checkout
    {
        $this->failIfDown();

        return $this->checkouts[$id] ?? throw new PaymentGatewayException('The payment service doesn\'t know this payment.');
    }

    public function expireCheckout(string $id): Checkout
    {
        $checkout = $this->findCheckout($id);
        if ($checkout->status !== CheckoutStatus::Open) {
            return $checkout;
        }

        return $this->checkouts[$id] = $this->with($checkout, status: CheckoutStatus::Expired);
    }

    public function signatureHeader(): string
    {
        return 'X-Fake-Signature';
    }

    public function parseWebhook(string $payload, string $signature, string $secret): ?PaymentEvent
    {
        if ($secret === '' || ! hash_equals(hash_hmac('sha256', $payload, $secret), $signature)) {
            throw new InvalidWebhookSignature('The webhook signature does not match.');
        }
        $event = json_decode($payload, true);
        $type = PaymentEventType::tryFrom((string) ($event['type'] ?? ''));
        $checkout = $this->checkouts[(string) ($event['checkout'] ?? '')] ?? null;

        return $type === null || $checkout === null ? null : new PaymentEvent((string) $event['id'], $type, $checkout);
    }

    /** The player pays (or pays a different amount, to test the amount check). */
    public function pay(string $id, ?int $amountSen = null): Checkout
    {
        $checkout = $this->checkouts[$id];

        return $this->checkouts[$id] = $this->with($checkout, status: CheckoutStatus::Complete, paid: true, amountSen: $amountSen ?? $checkout->amountSen);
    }

    /**
     * A signed webhook for a payment page, as the gateway would send it.
     *
     * @return array{string, array<string, string>} the body and the headers
     */
    public function webhook(string $checkoutId, PaymentEventType $type, string $secret, ?string $eventId = null): array
    {
        $payload = (string) json_encode([
            'id' => $eventId ?? 'evt_fake_'.$checkoutId.'_'.$type->value,
            'type' => $type->value,
            'checkout' => $checkoutId,
        ]);

        return [$payload, [$this->signatureHeader() => hash_hmac('sha256', $payload, $secret)]];
    }

    private function failIfDown(): void
    {
        if ($this->down) {
            throw new PaymentGatewayException;
        }
    }

    private function with(Checkout $checkout, CheckoutStatus $status, ?bool $paid = null, ?int $amountSen = null): Checkout
    {
        return new Checkout(
            id: $checkout->id,
            url: $status === CheckoutStatus::Open ? $checkout->url : null,
            status: $status,
            paid: $paid ?? $checkout->paid,
            amountSen: $amountSen ?? $checkout->amountSen,
            currency: $checkout->currency,
            bookingCode: $checkout->bookingCode,
            expiresAt: $checkout->expiresAt ?? CarbonImmutable::now(),
        );
    }
}
