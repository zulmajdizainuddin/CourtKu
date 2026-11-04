<?php

namespace App\Payments;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * A stand-in for Stripe for the browser tests (ADR-034): payment pages are kept in the cache, and
 * CourtKu shows its own "pretend Stripe" page (PretendCheckoutController) where the test presses
 * Pay. Everything after that is the real code: the player comes back, CourtKu asks the gateway how
 * the payment stands, and the booking is confirmed.
 *
 * Switched on with PAYMENT_GATEWAY=pretend, and never on the live site (see self::enabled()).
 */
class PretendGateway implements PaymentGateway
{
    public static function enabled(): bool
    {
        return config('services.payments.gateway') === 'pretend' && ! app()->isProduction();
    }

    public function createAccount(string $email, string $businessName, string $idempotencyKey): string
    {
        return 'acct_pretend_'.Str::lower(Str::random(12));
    }

    public function onboardingUrl(string $accountId, string $returnUrl, string $refreshUrl): string
    {
        // Nothing to fill in: straight back, as if the owner finished.
        return $returnUrl;
    }

    public function account(string $accountId): GatewayAccount
    {
        return new GatewayAccount($accountId, 'Pretend account', 'MY', chargesEnabled: true);
    }

    public function createCheckout(?string $accountId, CheckoutRequest $request): Checkout
    {
        $existing = Cache::get($this->keyFor($request->idempotencyKey));
        if (is_string($existing) && ($checkout = $this->load($existing)) !== null) {
            return $checkout; // the same key twice = the same payment page, like Stripe
        }

        $id = 'cs_pretend_'.Str::lower(Str::random(24));
        // The pretend page sits under /api on the web app's own address, like every other API call.
        $origin = rtrim((string) preg_replace('#^(https?://[^/]+).*$#', '$1', $request->returnUrl), '/');
        $this->save([
            'id' => $id,
            'url' => "{$origin}/api/pretend-checkout/{$id}",
            'status' => CheckoutStatus::Open->value,
            'paid' => false,
            'amount_sen' => $request->amountSen,
            'booking_code' => $request->bookingCode,
            'description' => $request->description,
            'return_url' => $request->returnUrl,
            'expires_at' => $request->expiresAt->toIso8601String(),
        ]);
        Cache::put($this->keyFor($request->idempotencyKey), $id, now()->addDay());

        return $this->findCheckout($accountId, $id);
    }

    public function findCheckout(?string $accountId, string $id): Checkout
    {
        return $this->load($id) ?? throw new PaymentGatewayException('The payment service doesn\'t know this payment.');
    }

    public function expireCheckout(?string $accountId, string $id): Checkout
    {
        $data = $this->data($id);
        if ($data !== null && $data['status'] === CheckoutStatus::Open->value) {
            $this->save([...$data, 'status' => CheckoutStatus::Expired->value]);
        }

        return $this->findCheckout($accountId, $id);
    }

    /**
     * What the pretend page shows and where it goes back to, or null for an unknown page.
     *
     * @return array<string, mixed>|null
     */
    public function page(string $id): ?array
    {
        return $this->data($id);
    }

    /** The "Pay" button on the pretend page: the money is taken, if the page is still open. */
    public function pay(string $id): void
    {
        $data = $this->data($id);
        if ($data !== null && $data['status'] === CheckoutStatus::Open->value) {
            $this->save([...$data, 'status' => CheckoutStatus::Complete->value, 'paid' => true]);
        }
    }

    public function signatureHeader(): string
    {
        return 'X-Pretend-Signature';
    }

    public function parseWebhook(string $payload, string $signature, string $secret): ?PaymentEvent
    {
        // No webhooks: CourtKu finds out by asking when the player comes back.
        throw new InvalidWebhookSignature;
    }

    private function load(string $id): ?Checkout
    {
        $data = $this->data($id);
        if ($data === null) {
            return null;
        }
        $status = CheckoutStatus::from($data['status']);

        return new Checkout(
            id: $data['id'],
            url: $status === CheckoutStatus::Open ? $data['url'] : null,
            status: $status,
            paid: $data['paid'],
            amountSen: $data['amount_sen'],
            currency: 'myr',
            bookingCode: $data['booking_code'],
            expiresAt: CarbonImmutable::parse($data['expires_at']),
        );
    }

    /** @return array<string, mixed>|null */
    private function data(string $id): ?array
    {
        $data = Cache::get("pretend-checkout:{$id}");

        return is_array($data) ? $data : null;
    }

    /** @param  array<string, mixed>  $data */
    private function save(array $data): void
    {
        if (! self::enabled()) {
            throw new RuntimeException('The pretend payment gateway is for tests only.');
        }
        Cache::put("pretend-checkout:{$data['id']}", $data, now()->addDay());
    }

    private function keyFor(string $idempotencyKey): string
    {
        return "pretend-checkout-key:{$idempotencyKey}";
    }
}
