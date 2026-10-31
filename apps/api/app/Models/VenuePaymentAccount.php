<?php

namespace App\Models;

use App\Payments\GatewayAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A venue's own Stripe account, connected through Stripe Connect (ADR-009, ADR-024). The money goes
 * straight to it. CourtKu stores no secret for it, only its id: payments are made on the venue's
 * behalf with CourtKu's own platform key, which lives only in the server's settings.
 *
 * @property int $venue_id
 * @property string $gateway "stripe"
 * @property string $account_id "acct_1Abc..."
 * @property string|null $account_name
 * @property bool $charges_enabled
 * @property bool $is_live
 * @property Carbon|null $checked_at
 * @property int|null $connected_by
 * @property Carbon $created_at
 */
class VenuePaymentAccount extends Model
{
    protected function casts(): array
    {
        return [
            'charges_enabled' => 'boolean',
            'is_live' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** True once Stripe says the account can take payments. */
    public function isReady(): bool
    {
        return $this->charges_enabled;
    }

    /** Saves what Stripe just said about the account. */
    public function updateFrom(GatewayAccount $account): void
    {
        $this->forceFill([
            'account_name' => $account->name ?? $this->account_name,
            'charges_enabled' => $account->chargesEnabled,
            'checked_at' => now(),
        ])->save();
    }
}
