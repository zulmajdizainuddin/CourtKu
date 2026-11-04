<?php

namespace App\Http\Resources;

use App\Models\Court;
use App\Models\Venue;
use App\Services\Cloudinary;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One venue in the guest list on the home page (ADR-031): enough to choose where to play.
 * Expects its bookable courts (with sports) and photos loaded.
 *
 * @mixin Venue
 */
class VenueCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $courts = $this->courts;
        // With a sport chosen, the price is for that sport: a futsal pitch, not the badminton court next to it.
        $sport = $request->query('sport');
        $priced = is_string($sport) && $sport !== ''
            ? $courts->filter(fn (Court $court) => $court->sports->contains('slug', $sport))
            : $courts;
        $cheapest = (int) $priced->min('base_price_sen');
        $cover = $this->photos->sortBy([['sort_order', 'asc'], ['id', 'asc']])->first();

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'city' => $this->city,
            'state' => $this->state,
            'is_demo' => (bool) $this->is_demo,
            'sports' => $courts->flatMap(fn (Court $court) => $court->sports->pluck('slug'))->unique()->sort()->values(),
            // The sport most of its courts offer, for the drawn court picture when there is no photo.
            'main_sport' => $courts->flatMap(fn (Court $court) => $court->sports->pluck('slug'))->countBy()
                ->sortKeys()->sortDesc()->keys()->first(),
            // The cheapest court's normal price per hour (peak hours can cost more), for the chosen sport if any.
            'from_price_sen' => $cheapest,
            'from_price' => Money::format($cheapest),
            'cover_url' => $cover ? app(Cloudinary::class)->url($cover->public_id, 'c_fill,w_600,h_400') : null,
        ];
    }
}
