<?php

namespace App\Payments;

/**
 * What CourtKu needs from an online payment service (plan section 9). The booking code never
 * talks to Stripe directly, only to this, so another gateway (Billplz, ToyyibPay) is one more
 * class, not a rewrite (ADR-023).
 *
 * The player always pays on the gateway's own page (ADR-006): CourtKu never sees card or bank
 * details. Only a signed webhook (or asking the gateway itself) confirms a payment, never the
 * player's browser coming back.
 *
 * Each venue has its own gateway account (ADR-009), so a gateway object is made with that
 * venue's secret key.
 */
interface PaymentGateway
{
    /** Opens a payment page for one booking and returns where to send the player. */
    public function createCheckout(CheckoutRequest $request): Checkout;

    /** Asks the gateway how a payment page stands now: still open, paid, or expired. */
    public function findCheckout(string $id): Checkout;

    /**
     * Closes a payment page that is still open, e.g. when the hold ran out, so the player can't
     * pay for time that is no longer theirs. Returns how it stands afterwards: already paid stays paid.
     */
    public function expireCheckout(string $id): Checkout;

    /** The request header that carries the webhook's signature, e.g. "Stripe-Signature". */
    public function signatureHeader(): string;

    /**
     * Checks a webhook's signature with the venue's webhook secret and reads it.
     * Returns null for events CourtKu doesn't act on.
     *
     * @throws InvalidWebhookSignature when the signature is missing, wrong or too old
     */
    public function parseWebhook(string $payload, string $signature, string $secret): ?PaymentEvent;
}
