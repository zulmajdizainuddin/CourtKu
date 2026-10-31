<?php

namespace App\Http\Controllers\V1\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClosureRequest;
use App\Http\Resources\VenueClosureResource;
use App\Models\Venue;
use App\Models\VenueClosure;
use App\Services\BookingConflicts;
use App\Support\MalaysiaTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Days a venue is closed, e.g. Hari Raya (FR-07). Owners add and remove them; staff can read them.
 */
class VenueClosureController extends Controller
{
    /** GET /api/v1/manage/venues/{venue}/closures: today's and future closures, soonest first. */
    public function index(Venue $venue): AnonymousResourceCollection
    {
        $closures = $venue->closures()
            ->where('ends_on', '>=', MalaysiaTime::today())
            ->orderBy('starts_on')
            ->get();

        return VenueClosureResource::collection($closures);
    }

    /** POST /api/v1/manage/venues/{venue}/closures */
    public function store(StoreClosureRequest $request, Venue $venue, BookingConflicts $conflicts): JsonResponse
    {
        // FR-38: a closure can't silently cancel bookings; the owner cancels them first.
        $stranded = $conflicts->onDays($venue, $request->string('starts_on')->value(), $request->string('ends_on')->value());
        if ($stranded->isNotEmpty()) {
            throw BookingConflicts::refuse($stranded, '{is} on these days', 'add the closure again');
        }
        $closure = $venue->closures()->create($request->validated());

        return (new VenueClosureResource($closure))->response()->setStatusCode(201);
    }

    /**
     * DELETE /api/v1/manage/venues/{venue}/closures/{closure}
     * The route only finds closures of this venue (scopeBindings), so venue A's URL
     * can never remove venue B's closure.
     */
    public function destroy(Venue $venue, VenueClosure $closure): Response
    {
        $closure->delete();

        return response()->noContent();
    }
}
