<?php

namespace App\Http\Controllers\V1;

use App\Enums\BookingSource;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\PlayerBookingResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use App\Services\BookingService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Players booking courts online (FR-15, FR-35, FR-36): logged in, with a verified email.
 * In M3 every booking is paid at the venue, so it is confirmed straight away (ADR-021).
 */
class BookingController extends Controller
{
    /**
     * POST /api/v1/venues/{slug}/bookings  (header Idempotency-Key: <a random id per Book press>)
     * 201 with the new booking; 200 with the same booking when the same key comes again
     * (a double tap or a network retry); 409 SLOT_TAKEN when someone was faster.
     */
    public function store(StoreBookingRequest $request, Venue $venue, BookingService $bookings): JsonResponse
    {
        $player = $request->user();
        $key = (string) $request->header('Idempotency-Key');
        if ($key === '' || strlen($key) > 100) {
            throw new ApiException('IDEMPOTENCY_KEY_REQUIRED', 'Send an Idempotency-Key header (at most 100 characters) with each booking.', 400);
        }

        $earlier = $this->bookingWithKey($player->id, $key);
        if ($earlier !== null) {
            return (new PlayerBookingResource($earlier))->response()->setStatusCode(200);
        }

        $court = $venue->courts()->bookable()->with('sports')->find($request->integer('court_id'));
        if (! $court instanceof Court) {
            throw ValidationException::withMessages(['court_id' => 'This court can\'t be booked.']);
        }
        $sport = $court->sports->firstWhere('slug', $request->string('sport')->value());
        if (! $sport instanceof Sport) {
            throw ValidationException::withMessages(['sport' => "{$court->name} isn't for {$request->string('sport')}."]);
        }

        $draft = $bookings->prepare($court, $request->startsAt(), $request->integer('minutes'));
        try {
            $booking = $bookings->store($draft, BookingSource::Online, player: $player, createdBy: $player, sportId: $sport->id, idempotencyKey: $key);
        } catch (UniqueConstraintViolationException) {
            // The same key arrived twice at the same moment: the other request made the booking.
            return (new PlayerBookingResource($this->bookingWithKey($player->id, $key)))->response()->setStatusCode(200);
        }

        return (new PlayerBookingResource($booking))->response()->setStatusCode(201);
    }

    /** GET /api/v1/bookings/{code}: the player's own booking, e.g. for the confirmation page. */
    public function show(Request $request, string $code): PlayerBookingResource
    {
        $booking = Booking::where('code', $code)->where('user_id', $request->user()->id)->first();
        // Someone else's booking answers 404, exactly like a code that doesn't exist.
        abort_if($booking === null, 404);

        return new PlayerBookingResource($booking->load(['venue', 'court', 'sport']));
    }

    private function bookingWithKey(int $userId, string $key): ?Booking
    {
        return Booking::where('user_id', $userId)->where('idempotency_key', $key)->with(['venue', 'court', 'sport'])->first();
    }
}
