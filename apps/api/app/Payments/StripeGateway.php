<?php

namespace App\Payments;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Stripe Checkout, in test mode for the demo (ADR-023). Plain HTTPS calls to Stripe's API, so
 * every request is visible here and easy to fake in tests.
 *
 * Stripe's page shows whatever payment methods the venue's Stripe account allows (cards today,
 * FPX once the account is activated), so CourtKu never lists methods itself.
 *
 * Amounts: Stripe counts ringgit in sen, exactly like CourtKu (NFR-13).
 */
class StripeGateway implements PaymentGateway
{
    private const API = 'https://api.stripe.com/v1';

    /** Stripe refuses webhooks older than this; so do we, so an old captured webhook can't be replayed. */
    public const SIGNATURE_TOLERANCE_SECONDS = 300;

    /** Stripe keeps a payment page open for at least 30 minutes; CourtKu closes it itself when the hold runs out. */
    private const MIN_PAGE_MINUTES = 31;

    public function __construct(private readonly string $secretKey) {}

    public function createCheckout(CheckoutRequest $request): Checkout
    {
        $expiresAt = max($request->expiresAt, CarbonImmutable::now()->addMinutes(self::MIN_PAGE_MINUTES));

        $response = $this->send(fn () => $this->http()
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

    public function findCheckout(string $id): Checkout
    {
        return $this->checkout($this->send(fn () => $this->http()->get('/checkout/sessions/'.rawurlencode($id)))->json());
    }

    public function expireCheckout(string $id): Checkout
    {
        $response = $this->send(
            fn () => $this->http()->post('/checkout/sessions/'.rawurlencode($id).'/expire'),
            // Stripe answers 400 when the page is no longer open (paid or already expired): then say how it stands.
            allowBadRequest: true,
        );

        return $response->status() === 400 ? $this->findCheckout($id) : $this->checkout($response->json());
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

        return $type === null ? null : new PaymentEvent($event['id'], $type, $this->checkout($session));
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->withToken($this->secretKey)
            ->asForm()
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

        throw new PaymentGatewayException(match ($response->status()) {
            401 => 'The venue\'s Stripe secret key was refused. The owner should check it under Payments.',
            404 => 'The payment service doesn\'t know this payment.',
            default => 'The payment service didn\'t answer. Please try again in a minute.',
        });
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
