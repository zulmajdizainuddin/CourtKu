<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ManageBookingResource;
use App\Models\Court;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Support\MalaysiaTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The day schedule for the counter (FR-31): every court and every booking of one business day.
 * Owners and staff can see it.
 */
class ScheduleController extends Controller
{
    /**
     * GET /api/v1/manage/venues/{venue}/schedule?date=2026-11-06 (today in Malaysia when left out)
     *
     * times: the day's half hours, from opening to closing, after midnight too (ADR-015), plus any
     * half hour a booking holds outside today's hours. courts: the active courts, and any other
     * court with a booking that day. bookings: everything except cancelled ones.
     */
    public function show(Request $request, Venue $venue): JsonResponse
    {
        $day = $request->validate(['date' => ['sometimes', 'date_format:Y-m-d']])['date'] ?? MalaysiaTime::today();
        $date = AvailabilityService::date($day);
        $availability = new AvailabilityService($venue);

        $bookings = $venue->bookings()
            ->where('business_date', $day)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->with(['user', 'sport'])
            ->orderBy('starts_at')
            ->get();

        // Minutes after the day's midnight: 18:00 = 1080, Saturday 00:30 after a late Friday = 1470.
        $minutes = $availability->openMinutes($date);
        foreach ($bookings as $booking) {
            $start = (int) $date->diffInMinutes($booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE));
            $length = (int) $booking->starts_at->diffInMinutes($booking->ends_at);
            for ($minute = $start; $minute < $start + $length; $minute += 30) {
                $minutes[] = $minute;
            }
        }
        $minutes = array_values(array_unique($minutes));
        sort($minutes);

        $bookedCourts = $bookings->pluck('court_id')->unique()->all();
        $courts = $venue->courts()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $bookedCourts))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => [
            'date' => $day,
            'closed_reason' => $availability->closureOn($date)?->reason,
            'times' => array_map(function (int $minute) use ($date) {
                $startsAt = AvailabilityService::startsAt($date, $minute);

                return [
                    'starts_at' => $startsAt->toIso8601String(),
                    'time' => $startsAt->format('H:i'),
                    // After midnight on a late night: still this day's schedule (ADR-015).
                    'next_day' => $minute >= 1440,
                ];
            }, $minutes),
            'courts' => $courts->map(fn (Court $court) => [
                'id' => $court->id,
                'name' => $court->name,
                'is_active' => $court->is_active,
            ])->values(),
            'bookings' => ManageBookingResource::collection($bookings)->resolve(),
        ]]);
    }
}
