<?php

namespace App\Http\Controllers\V1\Admin;

use App\Enums\BookingStatus;
use App\Enums\VenueStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Venue;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/v1/admin/stats: platform totals for the admin (FR-33). Demo venues (venues.is_demo)
 * are left out, so the demo data never makes CourtKu look busier than it is.
 */
class StatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $venues = Venue::where('is_demo', false)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $today = CarbonImmutable::now(MalaysiaTime::TIMEZONE);
        $thisMonth = [$today->startOfMonth()->toDateString(), $today->endOfMonth()->toDateString()];

        // Booked = confirmed, checked in, completed or no-show, like the venue reports (VenueReport).
        $booked = fn (): Builder => Booking::query()
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::CheckedIn->value, BookingStatus::Completed->value, BookingStatus::NoShow->value])
            ->whereHas('venue', fn (Builder $venue) => $venue->where('is_demo', false));

        return response()->json(['data' => [
            'venues' => [
                'active' => (int) ($venues[VenueStatus::Active->value] ?? 0),
                'pending' => (int) ($venues[VenueStatus::Pending->value] ?? 0),
                'suspended' => (int) ($venues[VenueStatus::Suspended->value] ?? 0),
            ],
            'accounts' => User::count(),
            'bookings' => [
                'this_month' => $booked()->whereBetween('business_date', $thisMonth)->count(),
                'all_time' => $booked()->count(),
            ],
            'booked_sen' => [
                'this_month' => (int) $booked()->whereBetween('business_date', $thisMonth)->sum('total_sen'),
                'all_time' => (int) $booked()->sum('total_sen'),
            ],
        ]]);
    }
}
