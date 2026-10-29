<?php

namespace App\Http\Controllers\V1\Manage;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A venue's courts (FR-08). Owners add, change, reorder and remove them; staff can read them.
 * Routes with {court} only find courts of this {venue} (scopeBindings), so venue A's
 * address can never reach venue B's court.
 */
class CourtController extends Controller
{
    /** More courts than any real venue has; stops a script from filling the database. */
    private const MAX_COURTS = 50;

    /** GET /api/v1/manage/venues/{venue}/courts: in the owner's chosen order. */
    public function index(Venue $venue): AnonymousResourceCollection
    {
        $courts = $venue->courts()->with('sports')->orderBy('sort_order')->orderBy('id')->get();

        return CourtResource::collection($courts);
    }

    /** POST /api/v1/manage/venues/{venue}/courts: the new court goes at the end of the list. */
    public function store(CourtRequest $request, Venue $venue): JsonResponse
    {
        if ($venue->courts()->count() >= self::MAX_COURTS) {
            throw new ApiException('COURT_LIMIT', 'A venue can have at most '.self::MAX_COURTS.' courts.');
        }

        $court = DB::transaction(function () use ($request, $venue) {
            $court = $venue->courts()->create([
                ...$request->courtData(),
                'sort_order' => (int) $venue->courts()->max('sort_order') + 1,
            ]);
            $court->sports()->sync($request->sportIds());

            return $court;
        });

        return (new CourtResource($court->load('sports')))->response()->setStatusCode(201);
    }

    /** PUT /api/v1/manage/venues/{venue}/courts/{court}: name, indoor, active and sports. */
    public function update(CourtRequest $request, Venue $venue, Court $court): CourtResource
    {
        // TODO(M3, FR-38): refuse deactivating while the court has future bookings.
        DB::transaction(function () use ($request, $court) {
            $court->update($request->courtData());
            $court->sports()->sync($request->sportIds());
        });

        return new CourtResource($court->load('sports'));
    }

    /** DELETE /api/v1/manage/venues/{venue}/courts/{court} */
    public function destroy(Venue $venue, Court $court): Response
    {
        // TODO(M3): refuse once the court has bookings, so booking history is never lost.
        $court->delete();

        return response()->noContent();
    }

    /**
     * PUT /api/v1/manage/venues/{venue}/courts/order  { "court_ids": [7, 4, 5] }
     * Every court of the venue, each exactly once, in the new order.
     */
    public function reorder(Request $request, Venue $venue): AnonymousResourceCollection
    {
        $data = $request->validate([
            'court_ids' => ['required', 'array'],
            'court_ids.*' => ['integer', 'distinct'],
        ]);
        $ids = array_map(intval(...), $data['court_ids']);

        $venueCourtIds = $venue->courts()->pluck('id')->all();
        sort($venueCourtIds);
        $sent = $ids;
        sort($sent);
        if ($sent !== $venueCourtIds) {
            throw ValidationException::withMessages(['court_ids' => 'Send every court of this venue exactly once.']);
        }

        DB::transaction(function () use ($venue, $ids) {
            foreach ($ids as $position => $id) {
                $venue->courts()->whereKey($id)->update(['sort_order' => $position]);
            }
        });

        return $this->index($venue);
    }
}
