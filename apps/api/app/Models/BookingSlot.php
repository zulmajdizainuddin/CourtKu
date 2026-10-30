<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One taken half hour of one court (ADR-010), held by a booking or a block.
 * The unique index on (court_id, slot_start) makes a second claim on it fail.
 *
 * @property int $court_id
 * @property Carbon $slot_start UTC
 * @property int|null $booking_id
 * @property int|null $slot_block_id
 * @property int|null $price_sen
 */
#[Fillable(['court_id', 'slot_start', 'booking_id', 'slot_block_id', 'price_sen'])]
class BookingSlot extends Model
{
    protected function casts(): array
    {
        return [
            'court_id' => 'integer',
            'slot_start' => 'datetime',
            'booking_id' => 'integer',
            'slot_block_id' => 'integer',
            'price_sen' => 'integer',
        ];
    }
}
