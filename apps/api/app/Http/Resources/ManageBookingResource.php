<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A booking as the venue's owner and staff see it (FR-31): who booked, when, which court,
 * how much and its status. The customer is the player's account, or the name and phone staff
 * typed in for a walk-in or phone booking (FR-20, M3 PR 5).
 *
 * @mixin Booking
 */
class ManageBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'court_id' => $this->court_id,
            'sport' => $this->sport?->slug,
            'starts_at' => $this->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'ends_at' => $this->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'minutes' => (int) $this->starts_at->diffInMinutes($this->ends_at),
            'total_sen' => $this->total_sen,
            'total' => Money::format($this->total_sen),
            'customer' => [
                'name' => $this->user->name ?? $this->customer_name,
                'phone' => $this->user->phone ?? $this->customer_phone,
                'email' => $this->user?->email,
            ],
            'checked_in_at' => $this->checked_in_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
        ];
    }
}
