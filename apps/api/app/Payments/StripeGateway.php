<?php

namespace App\Payments;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Stripe Checkout with Stripe Connect, in test mode for the demo (ADR-023, ADR-024). Plain HTTPS
 * calls to Stripe's API, so every request is visible here and easy to fake in tests.
 *
 * Stripe's page shows whatever payment methods the venue's Stripe account allows (cards today,
 * FPX once the account is activated), so CourtKu never lists methods itself.
 *
 * Amounts: Stripe counts ringgit in sen, exactly like CourtKu (NFR-13).
 */
class StripeGateway implements PaymentGateway
{
    private const API = 'https://api.stripe.com/v1';

    /** Stripe's Accounts v2 (connected accounts) needs a pinned API version; v1 calls use the account's default. */
    private const V2_API = 'https://api.stripe.com';

    private const V2_VERSION = '2026-10-28.endive';

    /** Stripe refuses webhooks older than this; so do we, so an old captured webhook can't be replayed. */
    public const SIGNATURE_TOLERANCE_SECONDS = 300;

    /** Stripe keeps a payment page open for at least 30 minutes; CourtKu closes it itself when the hold runs out. */
    private const MIN_PAGE_MINUTES = 31;

    /** The Checkout events CourtKu acts on (see parseWebhook). The Connect webhook in Stripe sends these. */
    public const WEBHOOK_EVENTS = [
        'checkout.session.completed',
        'checkout.session.async_payment_succeeded',
        'checkout.session.async_payment_failed',
        'checkout.session.expired',
    ];

    /** $secretKey is CourtKu's own platform key (STRIPE_SECRET_KEY), never a venue's. */
    public function __construct(private readonly string $secretKey) {}

    /**
     * A Stripe account for a venue, made by CourtKu through Stripe Connect (ADR-024), with Stripe's
     * Accounts v2 API (Stripe no longer takes v1 for new Connect platforms). Its settings make it the
     * venue's own account in every way that matters:
     *   - dashboard full: the owner logs in to Stripe and runs it (payouts, refunds) themselves;
     *   - fees_collector stripe: Stripe takes its fees from the venue, as if the venue used Stripe directly;
     *   - losses_collector stripe: CourtKu doesn't carry the venue's refunds or disputes;
     *   - merchant configuration: the account takes card payments (its own Checkout pages).
     * The same Idempotency-Key twice gives the same account, so a double tap never makes two.
     */
    public function createAccount(string $email, string $businessName, string $idempotencyKey): string
    {
        $account = $this->send(fn () => $this->httpV2()
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post('/v2/core/accounts', [
                'display_name' => $businessName,
                'contact_email' => $email,
                'identity' => ['country' => 'MY'],
                'dashboard' => 'full',
                'defaults' => ['responsibilities' => ['fees_collector' => 'stripe', 'losses_collector' => 'stripe']],
                'configuration' => ['merchant' => ['capabilities' => ['card_payments' => ['requested' => true]]]],
            ]))->json();

        return is_string($account['id'] ?? null) ? $account['id'] : throw new PaymentGatewayException;
    }

    public function onboardingUrl(string $accountId, string $returnUrl, string $refreshUrl): string
    {
        $link = $this->send(fn () => $this->httpV2()->post('/v2/core/account_links', [
            'account' => $accountId,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => ['return_url' => $returnUrl, 'refresh_url' => $refreshUrl],
            ],
        ]))->json();

        return is_string($link['url'] ?? null) ? $link['url'] : throw new PaymentGatewayException;
    }

    /** Ready = Stripe has switched on card payments for the account (it says "active"). */
    public function account(string $accountId): GatewayAccount
    {
        $account = $this->send(fn () => $this->httpV2()->get('/v2/core/accounts/'.rawurlencode($accountId), [
            'include' => ['configuration.merchant', 'identity'],
        ]))->json();
        if (! is_string($account['id'] ?? null)) {
            throw new PaymentGatewayException;
        }

        return new GatewayAccount(
            id: $account['id'],
            name: is_string($account['display_name'] ?? null) ? $account['display_name'] : null,
            country: is_string($account['identity']['country'] ?? null) ? $account['identity']['country'] : null,
            chargesEnabled: ($account['configuration']['merchant']['capabilities']['card_payments']['status'] ?? null) === 'active',
        );
    }

    /**
     * A "direct charge": the payment page belongs to the venue's own account (the Stripe-Account header),
     * so the money lands there, not with CourtKu (ADR-009).
     */
    public function createCheckout(?string $accountId, CheckoutRequest $request): Checkout
    {
        $expiresAt = max($request->expiresAt, CarbonImmutable::now()->addMinutes(self::MIN_PAGE_MINUTES));

        $response = $this->send(fn () => $this->http($accountId)
            ->withHeaders(['Idempotency-Key' => $request->idempotencyKey])
            ->post('/checkout/sessions', [
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'myr',
                        'unit_amount' => $request->amountSen,
                        'product_data' => ['name' => $request->description],
                    ],
                ]],
                'customer_email' => $request->customerEmail,
                'client_reference_id' => $request->bookingCode,
                'metadata' => ['booking_code' => $request->bookingCode],
                'payment_intent_data' => ['metadata' => ['booking_code' => $request->bookingCode]],
                // The status page shows CourtKu's own booking status, never anything from this address (ADR-006).
                'success_url' => $request->returnUrl,
                'cancel_url' => $request->returnUrl,
                'expires_at' => $expiresAt->getTimestamp(),
            ]));

        return $this->checkout($response->json());
    }

    public function findCheckout(?string $accountId, string $id): Checkout
    {
        return $this->checkout($this->send(fn () => $this->http($accountId)->get('/checkout/sessions/'.rawurlencode($id)))->json());
    }

    public function expireCheckout(?string $accountId, string $id): Checkout
    {
        $response = $this->send(
            fn () => $this->http($accountId)->post('/checkout/sessions/'.rawurlencode($id).'/expire'),
            // Stripe answers 400 when the page is no longer open (paid or already expired): then say how it stands.
            allowBadRequest: true,
        );

        return $response->status() === 400 ? $this->findCheckout($accountId, $id) : $this->checkout($response->json());
    }

    public function signatureHeader(): string
    {
        return 'Stripe-Signature';
    }

    /**
     * Stripe signs every webhook (header "Stripe-Signature: t=1492774577,v1=5257a869...").
     *   1. Take the time t and every v1 signature from the header.
     *   2. Compute HMAC-SHA256 of "{t}.{the raw body}" with the endpoint's secret (whsec_...).
     *   3. It must equal one of the v1 values (hash_equals, so timing gives nothing away),
     *      and t must be within 5 minutes of now, so an old webhook can't be sent again.
     */
    public function parseWebhook(string $payload, string $signature, string $secret): ?PaymentEvent
    {
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($key === 't' && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 'v1' && $value !== '') {
                $signatures[] = $value;
            }
        }
        if ($timestamp === null || $signatures === [] || $secret === '') {
            throw new InvalidWebhookSignature('The webhook has no usable signature.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $matches = array_filter($signatures, fn (string $given) => hash_equals($expected, $given));
        if ($matches === []) {
            throw new InvalidWebhookSignature('The webhook signature does not match.');
        }
        if (abs(CarbonImmutable::now()->getTimestamp() - $timestamp) > self::SIGNATURE_TOLERANCE_SECONDS) {
            throw new InvalidWebhookSignature('The webhook is too old.');
        }

        $event = json_decode($payload, true);
        if (! is_array($event) || ! is_string($event['id'] ?? null) || ! is_array($event['data']['object'] ?? null)) {
            return null;
        }
        $session = $event['data']['object'];

        $type = match ($event['type'] ?? null) {
            // A card is paid at once; some methods (e.g. bank transfers) finish later, with "async_payment_succeeded".
            'checkout.session.completed' => ($session['payment_status'] ?? null) === 'paid' ? PaymentEventType::Paid : null,
            'checkout.session.async_payment_succeeded' => PaymentEventType::Paid,
            'checkout.session.async_payment_failed' => PaymentEventType::Failed,
            'checkout.session.expired' => PaymentEventType::Expired,
            default => null,
        };

        // A Connect webhook says which venue's account the event happened on.
        $accountId = is_string($event['account'] ?? null) ? $event['account'] : null;

        return $type === null ? null : new PaymentEvent($event['id'], $type, $this->checkout($session), $accountId);
    }

    /** With an account id, the request acts on that venue's account (Stripe Connect's Stripe-Account header). */
    private function http(?string $accountId = null): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->withToken($this->secretKey)
            ->when($accountId !== null, fn (PendingRequest $request) => $request->withHeaders(['Stripe-Account' => (string) $accountId]))
            ->asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    /** Stripe's v2 API: JSON in and out, with a pinned version. */
    private function httpV2(): PendingRequest
    {
        return Http::baseUrl(self::V2_API)
            ->withToken($this->secretKey)
            ->withHeaders(['Stripe-Version' => self::V2_VERSION])
            ->asJson()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    /**
     * Sends a request; a network failure or an error answer becomes a PaymentGatewayException (502).
     *
     * @param  Closure(): Response  $request
     */
    private function send(Closure $request, bool $allowBadRequest = false): Response
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            throw new PaymentGatewayException;
        }

        if ($response->successful() || ($allowBadRequest && $response->status() === 400)) {
            return $response;
        }

        throw match ($response->status()) {
            // CourtKu's own key: something for whoever runs CourtKu to fix, not the owner or the player.
            401 => new PaymentGatewayException('Online payments are not set up correctly on CourtKu. Please try again later.'),
            404 => new PaymentGatewayException('The payment service doesn\'t know this payment.'),
            default => new PaymentGatewayException,
        };
    }

    /**
     * Reads Stripe's Checkout Session into CourtKu's Checkout.
     *
     * @param  array<string, mixed>|null  $session
     */
    private function checkout(?array $session): Checkout
    {
        if (! is_string($session['id'] ?? null)) {
            throw new PaymentGatewayException;
        }

        return new Checkout(
            id: $session['id'],
            url: is_string($session['url'] ?? null) ? $session['url'] : null,
            status: CheckoutStatus::tryFrom((string) ($session['status'] ?? '')) ?? CheckoutStatus::Open,
            paid: ($session['payment_status'] ?? null) === 'paid',
            amountSen: (int) ($session['amount_total'] ?? 0),
            currency: (string) ($session['currency'] ?? ''),
            bookingCode: is_string($session['client_reference_id'] ?? null) ? $session['client_reference_id'] : null,
            expiresAt: is_int($session['expires_at'] ?? null) ? CarbonImmutable::createFromTimestamp($session['expires_at']) : null,
        );
    }
}
