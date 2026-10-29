<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Resources\ManageVenueResource;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * The venues a user works at, for the dashboard (owners and staff).
 */
class VenueController extends Controller
{
    /** GET /api/v1/manage/venues: the venue picker. Only venues this user belongs to. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $venues = $request->user()->venues()->orderBy('name')->get();

        return ManageVenueResource::collection($venues);
    }

    /**
     * POST /api/v1/manage/venues: list a new venue (FR-04).
     * It starts as pending and stays hidden until an admin approves it.
     */
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $user = $request->user();

        // One venue waiting for approval at a time, so nobody can flood the admin's queue.
        $hasPending = $user->venues()
            ->wherePivot('role', VenueRole::Owner->value)
            ->where('status', VenueStatus::Pending->value)
            ->exists();

        if ($hasPending) {
            throw new ApiException(
                'VENUE_PENDING_EXISTS',
                'You already have a venue waiting for approval. You can list another once it is approved.',
            );
        }

        // Both rows or neither: a venue must never exist without its owner.
        $venue = DB::transaction(function () use ($request, $user) {
            $venue = Venue::create($request->validated());
            $venue->members()->attach($user, ['role' => VenueRole::Owner]);

            return $venue;
        });

        return (new ManageVenueResource($venue->refresh()))->response()->setStatusCode(201);
    }

    /** GET /api/v1/manage/venues/{venue}. The route checks VenuePolicy::view first. */
    public function show(Venue $venue): ManageVenueResource
    {
        return new ManageVenueResource($venue);
    }
}
