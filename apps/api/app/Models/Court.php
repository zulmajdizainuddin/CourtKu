<?php

namespace App\Models;

use App\Models\Concerns\ClearsAvailabilityCache;
use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One court at a venue (FR-08). It may offer several sports, but it is still one
 * floor with one booking timeline, so its bookings can never overlap (ADR-014).
 *
 * @property bool $is_indoor
 * @property bool $is_active
 * @property int $sort_order
 * @property int|null $base_price_sen price per hour in sen (FR-09); null until the owner sets it
 */
#[Fillable(['name', 'is_indoor', 'base_price_sen', 'is_active', 'sort_order'])]
class Court extends Model
{
    use ClearsAvailabilityCache;

    /** @use HasFactory<CourtFactory> */
    use HasFactory;

    // The same defaults as the database columns, so a court that was just created
    // already knows them (otherwise is_active would be null until it is reloaded).
    protected $attributes = [
        'is_indoor' => true,
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_indoor' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'base_price_sen' => 'integer',
        ];
    }

    /**
     * Courts players can see and book: active, and with a price (FR-09).
     * An owner who hasn't set a price yet keeps the court out of players' sight.
     *
     * @param  Builder<self>  $query
     */
    public function scopeBookable(Builder $query): void
    {
        $query->where('is_active', true)->whereNotNull('base_price_sen');
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * @return BelongsToMany<Sport, $this>
     */
    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'court_sports')->orderBy('sports.id');
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
