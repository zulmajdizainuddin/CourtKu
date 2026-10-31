<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentSettingsRequest;
use App\Http\Resources\ManageVenueResource;
use App\Http\Resources\PaymentAccountResource;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Payments\PaymentGatewayException;
use App\Payments\PaymentGateways;
use App\Services\Holds;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The owner's Payments tab (FR-17, FR-39, ADR-009, ADR-024): connect the venue's own Stripe account
 * through Stripe Connect, and choose how players pay. Owners only (VenuePolicy::update).
 *
 * "Connect with Stripe":
 *   1. CourtKu asks Stripe to open an account for the venue and keeps only its id (acct_...).
 *   2. The owner goes to Stripe's own pages and gives their details to Stripe, not to CourtKu.
 *   3. They come back to the Payments tab, and CourtKu asks Stripe whether the account can take payments.
 * The owner never copies a key, and CourtKu never holds one of theirs.
 */
class PaymentAccountController extends Controller
{
    public function __construct(private readonly PaymentGateways $gateways) {}

    /**
     * GET /api/v1/manage/venues/{venue}/payment-account: the connected account, or null.
     * Until it can take payments, CourtKu asks Stripe again each time, e.g. when the owner comes back from Stripe.
     */
    public function show(Venue $venue): JsonResponse
    {
        $account = $venue->paymentAccount;
        if ($account !== null && ! $account->isReady()) {
            try {
                $account->updateFrom($this->gateways->make()->account($account->account_id));
            } catch (PaymentGatewayException) {
                // Stripe can't be reached right now: show what CourtKu last knew.
            }
        }

        return response()->json(['data' => $account === null ? null : new PaymentAccountResource($account)]);
    }

    /**
     * POST /api/v1/manage/venues/{venue}/payment-account/onboarding: { "data": { "url": "https://connect.stripe.com/..." } }
     * Where the owner goes next: Stripe's own sign-up pages for the venue's account. The first time,
     * the account is made first. Each link works once and for a few minutes, so a new one is made each time.
     */
    public function onboarding(Request $request, Venue $venue): JsonResponse
    {
        $gateway = $this->gateways->make();
        $account = $venue->paymentAccount;

        if ($account === null) {
            $accountId = $gateway->createAccount(
                email: $request->user()->email,
                businessName: $venue->name,
                // A double tap, or a retry after a network error, gets the same account at Stripe.
                idempotencyKey: "courtku-venue-{$venue->id}-account",
            );
            $account = new VenuePaymentAccount;
            $account->forceFill([
                'venue_id' => $venue->id,
                'gateway' => 'stripe',
                'account_id' => $accountId,
                'account_name' => $venue->name,
                'is_live' => false,
                'connected_by' => $request->user()->id,
            ])->save();
        }

        $page = rtrim((string) config('app.frontend_url'), '/')."/dashboard/{$venue->id}/payments";
        $url = $gateway->onboardingUrl($account->account_id, returnUrl: "{$page}?stripe=return", refreshUrl: "{$page}?stripe=expired");

        return response()->json(['data' => ['url' => $url]]);
    }

    /**
     * DELETE /api/v1/manage/venues/{venue}/payment-account: CourtKu stops using the account, and players
     * go back to paying at the counter. The Stripe account itself stays the venue's. Refused while a
     * player is paying, so their payment can still be confirmed.
     */
    public function destroy(Venue $venue): JsonResponse
    {
        $account = $venue->paymentAccount;
        abort_if($account === null, 404);

        (new Holds)->releaseExpired(venue: $venue);
        if ($venue->bookings()->where('status', BookingStatus::PendingPayment->value)->exists()) {
            throw new ApiException(
                'PAYMENTS_IN_PROGRESS',
                'A player is paying online right now. Try again in a few minutes, once their payment time is over.',
            );
        }

        $account->delete();
        $venue->forceFill(['payment_mode' => PaymentMode::Counter])->save();

        return response()->json(null, 204);
    }

    /** PUT /api/v1/manage/venues/{venue}/payment-settings  { "payment_mode": "both", "hold_minutes": 15 } */
    public function updateSettings(UpdatePaymentSettingsRequest $request, Venue $venue): ManageVenueResource
    {
        // Like the booking settings, these only shape new bookings: a hold already made keeps its time.
        $venue->update($request->validated());

        return new ManageVenueResource($venue);
    }
}
