<?php

namespace App\Http\Controllers\V1\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOpeningHoursRequest;
use App\Http\Resources\OperatingHourResource;
use App\Models\Venue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * A venue's weekly opening hours (FR-07). Owners change them; staff can read them.
 */
class OpeningHoursController extends Controller
{
    /** GET /api/v1/manage/venues/{venue}/hours */
    public function index(Venue $venue): AnonymousResourceCollection
    {
        $periods = $venue->operatingHours()->orderBy('weekday')->orderBy('opens_at')->get();

        return OperatingHourResource::collection($periods);
    }

    /** PUT /api/v1/manage/venues/{venue}/hours: replaces the whole week. */
    public function update(UpdateOpeningHoursRequest $request, Venue $venue): AnonymousResourceCollection
    {
        // TODO(M3, FR-38): refuse when future bookings would fall outside the new hours.

        // Old week out, new week in, as one step: players never see a half-saved week.
        DB::transaction(function () use ($request, $venue) {
            $venue->operatingHours()->delete();
            $venue->operatingHours()->createMany($request->periods());
        });

        return $this->index($venue);
    }
}
