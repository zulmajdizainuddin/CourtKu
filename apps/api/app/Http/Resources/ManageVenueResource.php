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
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
