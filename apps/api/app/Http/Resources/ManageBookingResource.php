<?php

namespace App\Http\Resources;

use App\Enums\BookingStatus;
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
            // While waiting for online payment: when the held time is freed (FR-16).
            'hold_expires_at' => $this->hold_expires_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            // Paid at the counter (FR-28): when, how and by whom.
            'paid_at' => $this->paid_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'payment_method' => $this->payment_method?->value,
            'paid_by' => $this->paidBy?->name,
            'cancel_reason' => $this->cancel_reason,
            // Which buttons the schedule shows, by the same rules the API checks.
            'actions' => [
                'check_in' => $this->canCheckIn(),
                'no_show' => $this->status === BookingStatus::Confirmed,
                'cancel' => in_array($this->status, [BookingStatus::Confirmed, BookingStatus::PendingPayment], true),
                'mark_paid' => $this->canMarkPaid(),
            ],
        ];
    }
}
