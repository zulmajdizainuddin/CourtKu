<?php

namespace App\Http\Resources;

use App\Models\VenuePaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A venue's connected Stripe account, as its owner sees it: which account, and whether it can take
 * payments yet. There is no secret to hide: CourtKu never holds one for it (ADR-024).
 *
 * @mixin VenuePaymentAccount
 */
class PaymentAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'gateway' => $this->gateway,
            'account_id' => $this->account_id,
            'account_name' => $this->account_name,
            // true = players can pay online; false = the owner still has to finish Stripe's sign-up pages.
            'ready' => $this->isReady(),
            'is_live' => $this->is_live,
            'connected_at' => $this->created_at->toIso8601String(),
        ];
    }
}
