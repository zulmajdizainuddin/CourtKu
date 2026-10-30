<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
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
 * @property Carbon $starts_at UTC
 * @property Carbon $ends_at UTC
 * @property Carbon $business_date
 * @property int $total_sen
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $checked_in_at
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
            'total_sen' => 'integer',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
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
