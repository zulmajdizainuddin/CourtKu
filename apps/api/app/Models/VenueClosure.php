<?php

namespace App\Models;

use App\Models\Concerns\ClearsAvailabilityCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Whole days the venue is closed, from starts_on to ends_on inclusive (FR-07).
 * Dates are the venue's own calendar (Malaysia), so there is no time zone to convert.
 *
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 */
#[Fillable(['starts_on', 'ends_on', 'reason'])]
class VenueClosure extends Model
{
    use ClearsAvailabilityCache;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
