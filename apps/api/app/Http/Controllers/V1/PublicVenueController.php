<?php

namespace App\Http\Controllers\V1;

use App\Enums\MalaysianState;
use App\Enums\VenueStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourtResource;
use App\Http\Resources\PublicVenueResource;
use App\Http\Resources\VenueCardResource;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * What guests see, without logging in (FR-04, FR-06): only approved (active) venues.
 * A pending or suspended venue answers 404, exactly like a web address that doesn't exist.
 *
 * @group Venues (public)
 */
class PublicVenueController extends Controller
{
    /**
     * GET /api/v1/venues?sport=badminton&state=selangor&q=cheras&page=2: the venues guests can book,
     * for the list on the home page (ADR-031). Only approved venues with at least one bookable court.
     * Real venues come first, then the demo venues (ADR-030); 24 per page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'sport' => ['nullable', 'string', Rule::exists('sports', 'slug')],
            'state' => ['nullable', Rule::enum(MalaysianState::class)],
            'q' => ['nullable', 'string', 'max:60'],
        ]);
        $sport = $filters['sport'] ?? null;
        // % and _ mean "anything" in LIKE: searching for them must find them as plain characters.
        $search = isset($filters['q']) ? str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($filters['q'])) : '';

        $venues = Venue::query()
            ->where('status', VenueStatus::Active->value)
            ->whereHas('courts', fn (Builder $court) => $court->bookable()
                ->when($sport, fn (Builder $court) => $court->whereHas('sports', fn (Builder $s) => $s->where('slug', $sport))))
            ->when($filters['state'] ?? null, fn (Builder $venue, string $state) => $venue->where('state', $state))
            ->when($search !== '', fn (Builder $venue) => $venue->where(fn (Builder $match) => $match
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")))
            ->with([
                'courts' => fn ($court) => $court->bookable()->with('sports'),
                'photos',
            ])
            ->orderBy('is_demo')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return VenueCardResource::collection($venues);
    }

    /**
     * GET /api/v1/venues/{slug}: everything the venue's public page shows.
     *
     * @urlParam venue_slug string required The venue's web address name (slug). Example: arena-demo
     */
    public function show(Venue $venue): PublicVenueResource
    {
        $this->ensurePublic($venue);

        return new PublicVenueResource($venue);
    }

    /**
     * GET /api/v1/venues/{slug}/courts: the courts players can book.
     *
     * @urlParam venue_slug string required The venue's web address name (slug). Example: arena-demo
     */
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
