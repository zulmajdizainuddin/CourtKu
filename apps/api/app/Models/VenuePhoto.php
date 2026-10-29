<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One photo of a venue (FR-06). The image is stored on Cloudinary (ADR-018);
 * this row only remembers its Cloudinary id, its size and its place in the gallery.
 *
 * @property string $public_id
 * @property int $width
 * @property int $height
 * @property int $sort_order
 */
#[Fillable(['public_id', 'width', 'height', 'sort_order'])]
class VenuePhoto extends Model
{
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
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
}
