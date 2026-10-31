<?php

namespace App\Payments;

use Carbon\CarbonImmutable;

/**
 * A pretend gateway for tests: keeps venue accounts and payment pages in memory and lets a test
 * finish an owner's sign-up or "pay", so the payment flow can be tested without the internet or a
 * Stripe account.
 *
 * Its webhooks are signed too (HMAC-SHA256 of the body with the secret, header X-Fake-Signature),
 * so tests also prove that unsigned or wrongly signed webhooks change nothing.
 */
class FakeGateway implements PaymentGateway
{
    /** @var array<string, GatewayAccount> venue accounts, by id */
    public array $accounts = [];

    /** @var array<string, Checkout> every payment page, by id */
    public array $checkouts = [];

    /** @var array<string, string|null> which venue account each payment page belongs to */
    public array $checkoutAccounts = [];

    /** @var list<CheckoutRequest> what CourtKu asked for, in order */
    public array $requests = [];

    /** How many times CourtKu asked how a payment page stands. */
    public int $lookups = 0;

    /** true = act as if the gateway can't be reached */
    public bool $down = false;

    /** @var array<string, string> what was made for each idempotency key */
    private array $idempotencyKeys = [];

    public function createAccount(string $email, string $businessName, string $idempotencyKey): string
    {
        $this->failIfDown();
        if (isset($this->idempotencyKeys[$idempotencyKey])) {
            return $this->idempotencyKeys[$idempotencyKey];
        }

        $id = 'acct_fake_'.(count($this->accounts) + 1);
        $this->accounts[$id] = new GatewayAccount($id, $businessName, 'MY', chargesEnabled: false);

        return $this->idempotencyKeys[$idempotencyKey] = $id;
    }

    public function onboardingUrl(string $accountId, string $returnUrl, string $refreshUrl): string
    {
        $this->failIfDown();

        return "https://connect.example.test/{$accountId}?return=".urlencode($returnUrl);
    }

    public function account(string $accountId): GatewayAccount
    {
        $this->failIfDown();

        return $this->accounts[$accountId] ?? throw new PaymentGatewayException('The payment service doesn\'t know this account.');
    }

    /** The owner finishes the gateway's sign-up pages, and the account can take payments. */
    public function finishOnboarding(string $accountId): void
    {
        $account = $this->accounts[$accountId];
        $this->accounts[$accountId] = new GatewayAccount($account->id, $account->name, $account->country, chargesEnabled: true);
    }

    public function createCheckout(?string $accountId, CheckoutRequest $request): Checkout
    {
        $this->failIfDown();
        $this->requests[] = $request;

        // The same key twice = the same page, like Stripe's Idempotency-Key.
        if (isset($this->idempotencyKeys[$request->idempotencyKey])) {
            return $this->checkouts[$this->idempotencyKeys[$request->idempotencyKey]];
        }

        $id = 'cs_fake_'.(count($this->checkouts) + 1);
        $this->idempotencyKeys[$request->idempotencyKey] = $id;
        $this->checkoutAccounts[$id] = $accountId;

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

    public function findCheckout(?string $accountId, string $id): Checkout
    {
        $this->failIfDown();
        $this->lookups++;
        // Like Stripe: a payment page is only found on the account it belongs to.
        if (! isset($this->checkouts[$id]) || $this->checkoutAccounts[$id] !== $accountId) {
            throw new PaymentGatewayException('The payment service doesn\'t know this payment.');
        }

        return $this->checkouts[$id];
    }

    public function expireCheckout(?string $accountId, string $id): Checkout
    {
        $checkout = $this->findCheckout($accountId, $id);
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
        $id = (string) ($event['checkout'] ?? '');
        $checkout = $this->checkouts[$id] ?? null;

        return $type === null || $checkout === null
            ? null
            : new PaymentEvent((string) $event['id'], $type, $checkout, $this->checkoutAccounts[$id] ?? null);
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
