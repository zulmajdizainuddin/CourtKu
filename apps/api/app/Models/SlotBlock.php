<?php

namespace App\Models;

use App\Models\Concerns\ClearsAvailabilityCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A time a court is closed for repairs or a private event (FR-21). Like a booking it holds its
 * half hours in booking_slots, so nobody can book them, and it can't be put over a booking.
 * Made and lifted only through BlockService.
 *
 * @property int $venue_id
 * @property int $court_id
 * @property Carbon $starts_at UTC
 * @property Carbon $ends_at UTC
 * @property Carbon $business_date
 * @property string $reason
 * @property int|null $created_by
 */
#[Fillable(['venue_id', 'court_id', 'starts_at', 'ends_at', 'business_date', 'reason', 'created_by'])]
class SlotBlock extends Model
{
    use ClearsAvailabilityCache;

    protected function casts(): array
    {
        return [
            'court_id' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'business_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<BookingSlot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}
