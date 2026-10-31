<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A refund the owner made in their Stripe dashboard and recorded in CourtKu (FR-25). Only ever added,
 * never changed.
 *
 * @property int $payment_id
 * @property int $venue_id
 * @property int $amount_sen
 * @property string $reason
 * @property int|null $recorded_by
 */
class Refund extends Model
{
    protected function casts(): array
    {
        return ['amount_sen' => 'integer'];
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
