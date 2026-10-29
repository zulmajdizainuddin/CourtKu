<?php

namespace App\Http\Resources;

use App\Models\VenueClosure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A closed period: { "id": 3, "starts_on": "2027-03-30", "ends_on": "2027-04-01", "reason": "Hari Raya" }
 *
 * @mixin VenueClosure
 */
class VenueClosureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'starts_on' => $this->starts_on->toDateString(),
            'ends_on' => $this->ends_on->toDateString(),
            'reason' => $this->reason,
        ];
    }
}
