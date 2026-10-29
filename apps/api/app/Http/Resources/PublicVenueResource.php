<?php

namespace App\Http\Resources;

use App\Models\Venue;
use App\Support\MalaysiaTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A venue as anyone on the internet sees it: the public page /v/{slug}.
 * Only what players need. Never the owner, staff, emails, status dates or the demo flag.
 *
 * @mixin Venue
 */
class PublicVenueResource extends JsonResource
{
    /** How many upcoming closures the page shows. */
    private const CLOSURES_SHOWN = 5;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address_line' => $this->address_line,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'state' => $this->state,
            'phone' => $this->phone,
            'facilities' => $this->facilities ?? [],
            'photos' => VenuePhotoResource::collection($this->photos()->orderBy('sort_order')->orderBy('id')->get()),
            'opening_hours' => OperatingHourResource::collection(
                $this->operatingHours()->orderBy('weekday')->orderBy('opens_at')->get(),
            ),
            'closures' => VenueClosureResource::collection(
                $this->closures()->where('ends_on', '>=', MalaysiaTime::today())->orderBy('starts_on')->limit(self::CLOSURES_SHOWN)->get(),
            ),
            // Inactive courts (e.g. under repair) are hidden from players.
            'courts' => CourtResource::collection(
                $this->courts()->where('is_active', true)->with('sports')->orderBy('sort_order')->orderBy('id')->get(),
            ),
        ];
    }
}
