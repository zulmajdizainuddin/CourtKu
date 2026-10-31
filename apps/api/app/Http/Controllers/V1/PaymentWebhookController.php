<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Payments\InvalidWebhookSignature;
use App\Payments\PaymentGateways;
use App\Services\PaymentProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /api/v1/webhooks/payments/stripe: Stripe tells CourtKu about payments on venues' accounts
 * (one Connect webhook for every venue, ADR-024). The only thing that confirms a payment (ADR-006).
 *
 *   wrong or missing signature -> 401, logged, nothing changes (plan section 9);
 *   an event CourtKu doesn't act on, or one already acted on -> 200, nothing changes;
 *   otherwise -> saved, acted on, 200.
 *
 * Stripe retries a webhook that doesn't get a 2xx, so a valid one is always answered quickly with 200.
 */
class PaymentWebhookController extends Controller
{
    public function stripe(Request $request, PaymentGateways $gateways, PaymentProcessor $processor): JsonResponse
    {
        $gateway = $gateways->make();

        try {
            $event = $gateway->parseWebhook(
                $request->getContent(), // the raw body: the signature covers it byte for byte
                (string) $request->header($gateway->signatureHeader()),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (InvalidWebhookSignature $e) {
            Log::warning('Payment webhook refused: '.$e->getMessage(), ['ip' => $request->ip()]);
            throw new ApiException('INVALID_SIGNATURE', 'The webhook signature is not valid.', 401);
        }

        if ($event !== null) {
            $saved = $processor->save('stripe', $event);
            if ($saved !== null) {
                $processor->process($saved);
            }
        }

        return response()->json(['received' => true]);
    }
}
