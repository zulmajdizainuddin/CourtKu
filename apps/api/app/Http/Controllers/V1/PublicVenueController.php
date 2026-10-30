<?php

namespace App\Http\Controllers\V1;

use App\Enums\VenueStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourtResource;
use App\Http\Resources\PublicVenueResource;
use App\Models\Venue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * What guests see, without logging in (FR-04, FR-06): only approved (active) venues.
 * A pending or suspended venue answers 404, exactly like a web address that doesn't exist.
 */
class PublicVenueController extends Controller
{
    /** GET /api/v1/venues/{slug}: everything the venue's public page shows. */
    public function show(Venue $venue): PublicVenueResource
    {
        $this->ensurePublic($venue);

        return new PublicVenueResource($venue);
    }

    /** GET /api/v1/venues/{slug}/courts: the courts players can book. */
    public function courts(Venue $venue): AnonymousResourceCollection
    {
        $this->ensurePublic($venue);

        return CourtResource::collection(
            $venue->courts()->bookable()->with('sports')->orderBy('sort_order')->orderBy('id')->get(),
        );
    }

    private function ensurePublic(Venue $venue): void
    {
        abort_unless($venue->status === VenueStatus::Active, 404);
    }
}
