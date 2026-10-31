<?php

namespace App\Payments;

/**
 * Hands out the payment gateway, made with CourtKu's own platform key (ADR-024). Venues never give
 * CourtKu a key: their accounts are connected through Stripe Connect.
 *
 * Test keys only (ADR-023), so the demo can never move real money. Tests swap in a FakeGateway with
 * PaymentGateways::fake(), so no test ever reaches Stripe.
 */
class PaymentGateways
{
    public function __construct(private readonly ?PaymentGateway $fake = null) {}

    /** For tests: bind it with $this->app->instance(PaymentGateways::class, PaymentGateways::fake($gateway)). */
    public static function fake(PaymentGateway $gateway): self
    {
        return new self($gateway);
    }

    public function make(): PaymentGateway
    {
        if ($this->fake !== null) {
            return $this->fake;
        }

        $key = (string) config('services.stripe.secret');
        if (! str_starts_with($key, 'sk_test_')) {
            throw new PaymentGatewayException('Online payments are not set up on this CourtKu server yet.');
        }

        return new StripeGateway($key);
    }
}
