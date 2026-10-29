<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One opening period on one weekday, e.g. Friday 08:00 to 12:30 (FR-07, ADR-016).
 *
 * @property int $weekday 0 = Sunday ... 6 = Saturday
 * @property string $opens_at "08:00:00"
 * @property string $closes_at "02:00:00"
 * @property bool $closes_next_day
 */
#[Fillable(['weekday', 'opens_at', 'closes_at', 'closes_next_day'])]
class OperatingHour extends Model
{
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'closes_next_day' => 'boolean',
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
