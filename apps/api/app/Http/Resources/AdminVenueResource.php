<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A venue as the platform admin sees it in the approval list (FR-33).
 *
 * @mixin Venue
 */
class AdminVenueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'state' => $this->state,
            'owners' => $this->whenLoaded('owners', fn () => $this->owners->map(fn (User $owner) => [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'suspended_at' => $this->suspended_at?->toIso8601String(),
        ];
    }
}
