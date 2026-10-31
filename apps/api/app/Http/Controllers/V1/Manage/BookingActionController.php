<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ManageBookingResource;
use App\Models\Booking;
use App\Models\Venue;
use App\Services\BookingStateMachine;
use App\Support\MalaysiaTime;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What staff and owners do with a booking at the counter (FR-28, plan's permission table):
 * check in, mark a no-show, mark it paid, or cancel it. Every change goes through
 * BookingStateMachine (row lock + audit log).
 */
class BookingActionController extends Controller
{
    public function __construct(private readonly BookingStateMachine $machine) {}

    /** POST .../bookings/{code}/check-in: the player arrived. From an hour before the start until it ends. */
    public function checkIn(Request $request, Venue $venue, Booking $booking): ManageBookingResource
    {
        if ($booking->status === BookingStatus::Confirmed && ! $booking->canCheckIn()) {
            $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
            throw new ApiException(
                'CHECK_IN_NOT_OPEN',
                now()->lessThan($booking->starts_at)
                    ? 'Check-in opens 1 hour before the start, at '.$start->subHour()->format('j M, g:i a').'.'
                    : 'This booking has already ended, so it can\'t be checked in. Mark it as a no-show instead.',
            );
        }

        return $this->respond($this->machine->transition($booking, BookingStatus::CheckedIn, $request->user()));
    }

    /** POST .../bookings/{code}/no-show: the player didn't come. The time stays taken, as history. */
    public function noShow(Request $request, Venue $venue, Booking $booking): ManageBookingResource
    {
        return $this->respond($this->machine->transition($booking, BookingStatus::NoShow, $request->user()));
    }

    /** POST .../bookings/{code}/paid  { "method": "cash" | "qr" | "card" } */
    public function markPaid(Request $request, Venue $venue, Booking $booking): ManageBookingResource
    {
        $data = $request->validate(
            ['method' => ['required', Rule::enum(PaymentMethod::class)->only(PaymentMethod::atCounter())]],
            ['method.required' => 'Choose how they paid.', 'method.enum' => 'Choose cash, QR / DuitNow or card.'],
        );

        return $this->respond($this->machine->markPaid($booking, PaymentMethod::from($data['method']), $request->user()));
    }

    /**
     * POST .../bookings/{code}/cancel  { "reason": "Rain" }
     * Staff and owners can cancel any confirmed booking, or one waiting for payment, with no cutoff
     * (unlike players, FR-24).
     * The time is free for others straight away.
     */
    public function cancel(Request $request, Venue $venue, Booking $booking): ManageBookingResource
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'max:200']],
            ['reason.required' => 'Say why, e.g. rain or the player asked.'],
        );

        return $this->respond($this->machine->transition($booking, BookingStatus::Cancelled, $request->user(), trim($data['reason'])));
    }

    private function respond(Booking $booking): ManageBookingResource
    {
        return new ManageBookingResource($booking->load(['user', 'sport', 'paidBy']));
    }
}
