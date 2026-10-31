<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A booking as its player sees it: { "code": "CK-7F3K2", "status": "confirmed",
 * "venue": { "name", "slug", "phone" }, "court": { "id", "name" }, "sport": "badminton",
 * "starts_at": "2026-11-06T18:00:00+08:00", ..., "total": "RM 30.00", "pay_at_venue": true }
 * Never the venue's staff or other players.
 *
 * @mixin Booking
 */
class PlayerBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status->value,
            // While waiting for payment: when the held time is freed (FR-16).
            'hold_expires_at' => $this->hold_expires_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'venue' => [
                'name' => $this->venue->name,
                'slug' => $this->venue->slug,
                'phone' => $this->venue->phone,
            ],
            'court' => ['id' => $this->court->id, 'name' => $this->court->name],
            'sport' => $this->sport?->slug,
            // Shown in Malaysia time; stored in UTC.
            'starts_at' => $this->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'ends_at' => $this->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'business_date' => $this->business_date->toDateString(),
            'minutes' => (int) $this->starts_at->diffInMinutes($this->ends_at),
            'total_sen' => $this->total_sen,
            'total' => Money::format($this->total_sen),
            // In M3 every booking is paid at the venue (ADR-021).
            'pay_at_venue' => true,
            // FR-24: whether the player can still cancel online, and until when.
            'can_cancel' => $this->playerCanCancel(),
            'cancel_until' => $this->playerCancelDeadline()->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
        ];
    }
}
