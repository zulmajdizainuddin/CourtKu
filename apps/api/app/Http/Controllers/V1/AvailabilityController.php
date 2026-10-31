<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilityRequest;
use App\Http\Requests\QuoteRequest;
use App\Models\Court;
use App\Models\Venue;
use App\Services\AvailabilityCache;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * What players see before booking, without logging in: free times and prices (FR-11, FR-12).
 * Only approved (active) venues; any other answers 404 (checked in the requests, before the input).
 */
class AvailabilityController extends Controller
{
    /**
     * GET /api/v1/venues/{slug}/availability?date=2026-11-06&sport=badminton
     * Every bookable court offering the sport, with each half hour players can still book
     * and its price. Half hours that have started, or are inside the venue's notice, are left out.
     */
    public function show(AvailabilityRequest $request, Venue $venue): JsonResponse
    {
        $date = AvailabilityService::date($request->string('date')->value());
        $sport = $request->string('sport')->value();

        // Kept for 30 seconds, and thrown away as soon as a booking or setting changes (ADR-028).
        return response()->json(['data' => AvailabilityCache::remember($venue, $date->toDateString(), $sport,
            fn () => $this->grid($venue, $date, $sport))]);
    }

    /**
     * @return array<string, mixed>
     */
    private function grid(Venue $venue, CarbonImmutable $date, string $sport): array
    {
        $availability = new AvailabilityService($venue);
        $pricing = PricingService::forVenue($venue);
        $minutes = $availability->bookableMinutes($date);

        $courts = $venue->courts()
            ->bookable()
            ->when($sport !== '', fn ($query) => $query->whereHas('sports', fn ($q) => $q->where('slug', $sport)))
            ->with('sports')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $taken = $availability->takenSlots($courts->modelKeys(), $date);

        return [
            'date' => $date->toDateString(),
            // The closure's reason (e.g. "Hari Raya") when the venue is closed that day.
            'closed_reason' => $availability->closureOn($date)?->reason,
            'slot_minutes' => $venue->slot_minutes,
            'min_minutes' => $venue->min_minutes,
            'max_minutes' => $venue->max_minutes,
            'courts' => $courts->map(fn (Court $court) => [
                'id' => $court->id,
                'name' => $court->name,
                'is_indoor' => $court->is_indoor,
                'sports' => $court->sports->pluck('slug'),
                'cells' => array_map(fn (int $minute) => [
                    // The real time, e.g. Saturday 00:30 for a late Friday: "2026-11-07T00:30:00+08:00".
                    'starts_at' => AvailabilityService::startsAt($date, $minute)->toIso8601String(),
                    'time' => AvailabilityService::startsAt($date, $minute)->format('H:i'),
                    // This half hour's price in sen: half the hourly price that applies then.
                    'price_sen' => $pricing->cellPriceSen($court, $date->dayOfWeek, $minute),
                    // false when a booking already holds this half hour.
                    'available' => ! isset($taken[AvailabilityService::slotKey($court->id, AvailabilityService::startsAt($date, $minute))]),
                ], $minutes),
            ])->values()->all(),
        ];
    }

    /**
     * POST /api/v1/venues/{slug}/quote: the total for one booking, adding up each half hour.
     * The booking page shows this before the player books; M3 uses the same check to book.
     */
    public function quote(QuoteRequest $request, Venue $venue): JsonResponse
    {
        $court = $venue->courts()->bookable()->find($request->integer('court_id'));
        if (! $court instanceof Court) {
            throw ValidationException::withMessages(['court_id' => 'This court can\'t be booked.']);
        }

        $startsAt = $request->startsAt();
        $minutes = $request->integer('minutes');
        [$date, $startMinute] = (new AvailabilityService($venue))->check($court, $startsAt, $minutes);
        $total = PricingService::forVenue($venue)->priceSen($court, $date->dayOfWeek, $startMinute, $minutes);

        return response()->json(['data' => [
            'court_id' => $court->id,
            // The business day: a late Friday booking after midnight is still Friday's.
            'date' => $date->toDateString(),
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $startsAt->addMinutes($minutes)->toIso8601String(),
            'minutes' => $minutes,
            'price_sen' => $total,
            'price' => Money::format($total),
        ]]);
    }
}
