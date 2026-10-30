<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\BookingSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCounterBookingRequest;
use App\Http\Resources\ManageBookingResource;
use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Walk-in and phone bookings (FR-20): owners and staff book for a customer who has no account.
 * The same booking engine as online bookings, so they can never clash with them either.
 * Paid at the counter, confirmed straight away (ADR-021).
 */
class CounterBookingController extends Controller
{
    /** POST /api/v1/manage/venues/{venue}/bookings */
    public function store(StoreCounterBookingRequest $request, Venue $venue, BookingService $bookings): JsonResponse
    {
        $court = $venue->courts()->bookable()->with('sports')->find($request->integer('court_id'));
        if (! $court instanceof Court) {
            throw ValidationException::withMessages(['court_id' => 'This court can\'t be booked.']);
        }
        $sport = $court->sports->firstWhere('slug', $request->string('sport')->value());
        if (! $sport instanceof Sport) {
            throw ValidationException::withMessages(['sport' => "{$court->name} isn't for {$request->string('sport')}."]);
        }

        $draft = $bookings->prepare($court, $request->startsAt(), $request->integer('minutes'));
        $booking = $bookings->store(
            $draft,
            BookingSource::from($request->string('source')->value()),
            createdBy: $request->user(),
            sportId: $sport->id,
            customerName: $request->string('customer_name')->trim()->value(),
            customerPhone: $request->customerPhone(),
        );

        return (new ManageBookingResource($booking->load(['user', 'sport'])))->response()->setStatusCode(201);
    }
}
