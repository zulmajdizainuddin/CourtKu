<?php

namespace App\Http\Controllers\V1\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOpeningHoursRequest;
use App\Http\Resources\OperatingHourResource;
use App\Models\Venue;
use App\Services\BookingConflicts;
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
    public function update(UpdateOpeningHoursRequest $request, Venue $venue, BookingConflicts $conflicts): AnonymousResourceCollection
    {
        // FR-38: never leave an upcoming booking outside the opening hours.
        $stranded = $conflicts->outsideHours($venue, $request->periods());
        if ($stranded->isNotEmpty()) {
            throw BookingConflicts::refuse($stranded, 'would be outside the new opening hours', 'save the hours again');
        }

        // Old week out, new week in, as one step: players never see a half-saved week.
        DB::transaction(function () use ($request, $venue) {
            $venue->operatingHours()->delete();
            $venue->operatingHours()->createMany($request->periods());
        });

        return $this->index($venue);
    }
}
