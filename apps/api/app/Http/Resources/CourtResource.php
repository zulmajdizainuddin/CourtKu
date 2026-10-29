<?php

namespace App\Http\Resources;

use App\Models\Court;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A court: { "id": 4, "name": "Court 1", "is_indoor": true, "is_active": true,
 *            "sort_order": 0, "sports": ["badminton", "futsal"] }
 *
 * @mixin Court
 */
class CourtResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_indoor' => $this->is_indoor,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'sports' => $this->sports->map(fn (Sport $sport) => $sport->slug)->values(),
        ];
    }
}
