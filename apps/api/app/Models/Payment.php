<?php

namespace App\Models;

use App\Enums\PaymentProblem;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One online payment attempt for a booking (plan section 6). Only the gateway's signed webhook, or
 * asking the gateway itself, ever changes its status (ADR-006).
 *
 * @property int $booking_id
 * @property int $venue_id
 * @property string $gateway "stripe"
 * @property string $account_id the venue's Stripe account
 * @property string $checkout_id the gateway's payment page, e.g. "cs_test_..."
 * @property string|null $checkout_url where the player pays
 * @property int $amount_sen what the booking costs
 * @property string $currency "myr"
 * @property PaymentStatus $status
 * @property PaymentProblem|null $problem
 * @property int|null $paid_amount_sen
 * @property int $refunded_sen how much the owner has refunded so far (FR-25)
 * @property Carbon|null $paid_at
 * @property Carbon|null $checked_at when CourtKu last asked the gateway
 */
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'problem' => PaymentProblem::class,
            'amount_sen' => 'integer',
            'paid_amount_sen' => 'integer',
            'refunded_sen' => 'integer',
            'paid_at' => 'datetime',
            'checked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The refunds the owner recorded (FR-25).
     *
     * @return HasMany<Refund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /** What can still be refunded: what was paid, minus earlier refunds. */
    public function refundableSen(): int
    {
        return max(0, (int) $this->paid_amount_sen - $this->refunded_sen);
    }
}
