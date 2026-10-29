<?php

namespace App\Http\Controllers\V1\Admin;

use App\Enums\VenueStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminVenueResource;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * The platform admin's venue list: approve new venues, suspend bad ones (FR-04, FR-33).
 * Every route here is behind the "admin" gate (routes/api.php).
 */
class VenueController extends Controller
{
    /** GET /api/v1/admin/venues?status=pending: newest first, 25 per page. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['status' => ['nullable', Rule::enum(VenueStatus::class)]]);

        $venues = Venue::query()
            ->with('owners')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(25);

        return AdminVenueResource::collection($venues);
    }

    /** POST /api/v1/admin/venues/{venue}/approve */
    public function approve(Venue $venue): AdminVenueResource
    {
        $venue->approve();

        return new AdminVenueResource($venue->load('owners'));
    }

    /** POST /api/v1/admin/venues/{venue}/suspend */
    public function suspend(Venue $venue): AdminVenueResource
    {
        $venue->suspend();

        return new AdminVenueResource($venue->load('owners'));
    }
}
