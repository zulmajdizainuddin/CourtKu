<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A court booked for a time (FR-15, FR-20). Made only through BookingService, and its status
 * changed only through BookingStateMachine, so the rules live in one place each.
 *
 * status is deliberately not fillable: no request can set it directly.
 *
 * @property string $code "CK-7F3K2"
 * @property int $venue_id
 * @property int $court_id
 * @property int|null $user_id
 * @property int|null $sport_id
 * @property BookingSource $source
 * @property BookingStatus $status
 * @property Carbon|null $hold_expires_at while pending_payment: when the held time is freed (FR-16)
 * @property Carbon $starts_at UTC
 * @property Carbon $ends_at UTC
 * @property Carbon $business_date
 * @property int $total_sen
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $checked_in_at
 * @property Carbon|null $paid_at paid at the counter (FR-28)
 * @property PaymentMethod|null $payment_method
 * @property int|null $paid_by
 */
#[Fillable(['code', 'idempotency_key', 'venue_id', 'court_id', 'sport_id', 'user_id', 'customer_name', 'customer_phone', 'source', 'starts_at', 'ends_at', 'business_date', 'total_sen', 'created_by'])]
class Booking extends Model
{
    protected function casts(): array
    {
        return [
            'source' => BookingSource::class,
            'status' => BookingStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'business_date' => 'date',
            'hold_expires_at' => 'datetime',
            'total_sen' => 'integer',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'paid_at' => 'datetime',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * The sport played, e.g. badminton on a hall that also takes futsal (ADR-014).
     *
     * @return BelongsTo<Sport, $this>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** FR-24: the last moment the player may cancel online, e.g. 24 hours before the start. */
    public function playerCancelDeadline(): Carbon
    {
        return $this->starts_at->copy()->subHours($this->venue->cancel_cutoff_hours);
    }

    /**
     * A confirmed booking, before the venue's cutoff. After that the player calls the venue; staff can always cancel.
     * A booking still waiting for payment can always be cancelled: nothing was paid yet.
     */
    public function playerCanCancel(): bool
    {
        return match ($this->status) {
            BookingStatus::PendingPayment => true,
            BookingStatus::Confirmed => now()->lessThan($this->playerCancelDeadline()),
            default => false,
        };
    }

    /**
     * Who marked it paid at the counter.
     *
     * @return BelongsTo<User, $this>
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /** FR-28: from an hour before the start until it ends, so tomorrow's booking can't be checked in by mistake. */
    public function canCheckIn(): bool
    {
        return $this->status === BookingStatus::Confirmed
            && now()->greaterThanOrEqualTo($this->starts_at->copy()->subHour())
            && now()->lessThan($this->ends_at);
    }

    /**
     * A booking that isn't paid yet. A no-show can still pay, if the venue charges it. A booking waiting
     * for online payment can't be marked paid at the counter, and cancelled or expired ones can't be paid.
     */
    public function canMarkPaid(): bool
    {
        return in_array($this->status, [BookingStatus::Confirmed, BookingStatus::CheckedIn, BookingStatus::NoShow, BookingStatus::Completed], true)
            && $this->paid_at === null;
    }

    /**
     * The half hours this booking holds (ADR-010): a 2-hour booking has 4.
     *
     * @return HasMany<BookingSlot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}
