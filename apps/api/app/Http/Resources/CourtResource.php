<?php

namespace App\Http\Resources;

use App\Models\Court;
use App\Models\Sport;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A court: { "id": 4, "name": "Court 1", "is_indoor": true, "base_price_sen": 2500,
 *            "base_price": "RM 25.00", "is_active": true,
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
            // Money as whole sen, plus the same amount as text for display (plan section 8).
            'base_price_sen' => $this->base_price_sen,
            'base_price' => $this->base_price_sen === null ? null : Money::format($this->base_price_sen),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'sports' => $this->sports->map(fn (Sport $sport) => $sport->slug)->values(),
        ];
    }
}
