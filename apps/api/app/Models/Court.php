<?php

namespace App\Models;

use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One court at a venue (FR-08). It may offer several sports, but it is still one
 * floor with one booking timeline, so its bookings can never overlap (ADR-014).
 *
 * @property bool $is_indoor
 * @property bool $is_active
 * @property int $sort_order
 */
#[Fillable(['name', 'is_indoor', 'is_active', 'sort_order'])]
class Court extends Model
{
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
     * @return BelongsToMany<Sport, $this>
     */
    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'court_sports')->orderBy('sports.id');
    }
}
