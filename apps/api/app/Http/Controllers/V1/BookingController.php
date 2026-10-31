<?php

namespace App\Http\Controllers\V1;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\PlayerBookingResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Services\Holds;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

    /**
     * GET /api/v1/me/bookings?when=upcoming (soonest first) or ?when=past (latest first, with
     * cancelled ones), 20 at a time. The next page is ?cursor=<meta.next_cursor> (plan section 8).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $when = $request->validate(['when' => ['sometimes', 'in:upcoming,past']])['when'] ?? 'upcoming';
        $active = BookingStatus::activeValues();
        (new Holds)->releaseExpired(player: $request->user());

        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['venue', 'court', 'sport'])
            ->when(
                $when === 'upcoming',
                fn ($query) => $query->whereIn('status', $active)->where('ends_at', '>', now())
                    ->orderBy('starts_at')->orderBy('id'),
                fn ($query) => $query->where(fn ($q) => $q->whereNotIn('status', $active)->orWhere('ends_at', '<=', now()))
                    ->orderByDesc('starts_at')->orderByDesc('id'),
            )
            ->cursorPaginate(20);

        return PlayerBookingResource::collection($bookings);
    }

    /** GET /api/v1/bookings/{code}: the player's own booking, e.g. for the confirmation page. */
    public function show(Request $request, string $code): PlayerBookingResource
    {
        return new PlayerBookingResource($this->ownBooking($request, $code));
    }

    /**
     * POST /api/v1/bookings/{code}/cancel: the player cancels their own booking (FR-24), until the
     * venue's cutoff (e.g. 24 hours before). The half hours are free for others straight away.
     */
    public function cancel(Request $request, string $code, BookingStateMachine $machine): PlayerBookingResource
    {
        $booking = $this->ownBooking($request, $code);

        if ($booking->status === BookingStatus::Confirmed && ! $booking->playerCanCancel()) {
            $hours = $booking->venue->cancel_cutoff_hours;
            $phone = $booking->venue->phone === null ? '' : ' on '.$booking->venue->phone;
            throw new ApiException(
                'TOO_LATE_TO_CANCEL',
                "Online cancelling closed {$hours} hours before the start. Please call the venue{$phone}.",
            );
        }
        // Any other status (already cancelled, checked in...) is refused by the state machine.
        $machine->transition($booking, BookingStatus::Cancelled, $request->user(), 'Cancelled by the player');

        return new PlayerBookingResource($booking->fresh(['venue', 'court', 'sport']));
    }

    /**
     * Someone else's booking answers 404, exactly like a code that doesn't exist.
     * A hold whose payment time is up shows as expired, even before the every-minute tick.
     */
    private function ownBooking(Request $request, string $code): Booking
    {
        (new Holds)->releaseExpired(player: $request->user());
        $booking = Booking::where('code', $code)->where('user_id', $request->user()->id)->with(['venue', 'court', 'sport'])->first();
        abort_if($booking === null, 404);

        return $booking;
    }

    private function bookingWithKey(int $userId, string $key): ?Booking
    {
        return Booking::where('user_id', $userId)->where('idempotency_key', $key)->with(['venue', 'court', 'sport'])->first();
    }
}
