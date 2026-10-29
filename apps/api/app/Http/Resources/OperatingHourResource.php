<?php

namespace App\Http\Resources;

use App\Models\OperatingHour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One opening period: { "weekday": 5, "opens_at": "08:00", "closes_at": "02:00", "closes_next_day": true }
 *
 * @mixin OperatingHour
 */
class OperatingHourResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'weekday' => $this->weekday,
            // The database stores "08:00:00"; the seconds are always zero.
            'opens_at' => substr($this->opens_at, 0, 5),
            'closes_at' => substr($this->closes_at, 0, 5),
            'closes_next_day' => $this->closes_next_day,
        ];
    }
}
