<?php

namespace App\Http\Resources;

use App\Models\PriceRule;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A peak or off-peak price: { "id": 4, "court_id": null, "name": "Weekday peak",
 * "weekdays": [1,2,3,4,5], "starts_at": "18:00", "ends_at": "00:00", "ends_next_day": true,
 * "price_sen": 3000, "price": "RM 30.00" }
 *
 * @mixin PriceRule
 */
class PriceRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'court_id' => $this->court_id,
            'name' => $this->name,
            'weekdays' => $this->weekdays,
            // The database gives "18:00:00"; the forms use "18:00".
            'starts_at' => substr($this->starts_at, 0, 5),
            'ends_at' => substr($this->ends_at, 0, 5),
            'ends_next_day' => $this->ends_next_day,
            'price_sen' => $this->price_sen,
            'price' => Money::format($this->price_sen),
        ];
    }
}
