<?php

namespace App\Http\Resources;

use App\Models\Venue;
use App\Models\VenueMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A venue as its owner or staff see it in the dashboard, including their own role.
 *
 * @mixin Venue
 */
class ManageVenueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Loaded through $user->venues(), the role is already on the pivot row;
        // otherwise look it up (one query, only for single venues).
        $pivot = $this->resource->getRelationValue('pivot');
        $role = $pivot instanceof VenueMember ? $pivot->role : $this->roleOf($request->user());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'state' => $this->state,
            'role' => $role,
            'description' => $this->description,
            'address_line' => $this->address_line,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'phone' => $this->phone,
            'facilities' => $this->facilities ?? [],
            // Booking settings (FR-13).
            'slot_minutes' => $this->slot_minutes,
            'min_minutes' => $this->min_minutes,
            'max_minutes' => $this->max_minutes,
            'booking_window_days' => $this->booking_window_days,
            'min_notice_minutes' => $this->min_notice_minutes,
            'cancel_cutoff_hours' => $this->cancel_cutoff_hours,
            // Payments (FR-16, FR-17): how players pay, how long they have to pay online, and
            // whether the venue's own Stripe account is connected and can take payments.
            'payment_mode' => $this->payment_mode,
            'hold_minutes' => $this->hold_minutes,
            'payment_connected' => $this->paymentAccount?->isReady() ?? false,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
