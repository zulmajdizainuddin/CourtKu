<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Who did what to a booking and when (FR-34), e.g. "booking.cancelled by staff, reason: rain".
 * Rows are only ever added, never changed.
 *
 * @property string $action
 * @property array<string, mixed>|null $changes
 */
#[Fillable(['venue_id', 'user_id', 'action', 'subject_type', 'subject_id', 'changes'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    /**
     * @param  array<string, mixed>|null  $changes
     */
    public static function record(Booking $booking, string $action, ?User $by, ?array $changes = null): self
    {
        return self::create([
            'venue_id' => $booking->venue_id,
            'user_id' => $by?->id,
            'action' => $action,
            'subject_type' => $booking->getMorphClass(),
            'subject_id' => $booking->id,
            'changes' => $changes,
        ]);
    }
}
