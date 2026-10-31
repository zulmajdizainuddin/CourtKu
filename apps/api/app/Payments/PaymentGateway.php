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
 * Each venue has its own account at the gateway and the money goes straight to it (ADR-009).
 * CourtKu is the "platform": it connects venues' accounts and makes payments on their behalf
 * with its own key, so it never holds a venue's keys (ADR-024). $accountId says which venue's
 * account a payment belongs to; null means CourtKu's own account (only for trying things out).
 */
interface PaymentGateway
{
    /** Opens an account for a venue at the gateway (Stripe Connect). Returns its id, e.g. "acct_1Abc...". */
    public function createAccount(string $email, string $businessName, string $idempotencyKey): string;

    /**
     * A one-time link to the gateway's own sign-up pages, where the owner gives their details to the
     * gateway, not to CourtKu. $returnUrl is where they come back to; $refreshUrl is used when the link has expired.
     */
    public function onboardingUrl(string $accountId, string $returnUrl, string $refreshUrl): string;

    /** How a venue's account stands: its name, and whether it can take payments yet. */
    public function account(string $accountId): GatewayAccount;

    /** Opens a payment page for one booking, on the venue's account, and returns where to send the player. */
    public function createCheckout(?string $accountId, CheckoutRequest $request): Checkout;

    /** Asks the gateway how a payment page stands now: still open, paid, or expired. */
    public function findCheckout(?string $accountId, string $id): Checkout;

    /**
     * Closes a payment page that is still open, e.g. when the hold ran out, so the player can't
     * pay for time that is no longer theirs. Returns how it stands afterwards: already paid stays paid.
     */
    public function expireCheckout(?string $accountId, string $id): Checkout;

    /** The request header that carries the webhook's signature, e.g. "Stripe-Signature". */
    public function signatureHeader(): string;

    /**
     * Checks a webhook's signature with CourtKu's webhook secret and reads it.
     * Returns null for events CourtKu doesn't act on.
     *
     * @throws InvalidWebhookSignature when the signature is missing, wrong or too old
     */
    public function parseWebhook(string $payload, string $signature, string $secret): ?PaymentEvent;
}
