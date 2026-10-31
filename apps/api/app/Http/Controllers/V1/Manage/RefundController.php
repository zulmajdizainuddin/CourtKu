<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ManagePaymentResource;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Venue;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-25: refunds are recorded manually by the owner in the MVP. The owner refunds the player in their own
 * Stripe dashboard (the money is theirs, ADR-009), then records it here, so CourtKu's records match.
 * Owners only (VenuePolicy::update).
 */
class RefundController extends Controller
{
    /**
     * POST /api/v1/manage/venues/{venue}/payments/{payment}/refunds  { "amount_sen": 2000, "reason": "Paid after the time ran out" }
     * Part refunds are fine; the total can never be more than was paid. Fully refunded = status refunded.
     */
    public function store(Request $request, Venue $venue, Payment $payment): ManagePaymentResource
    {
        $data = $request->validate([
            'amount_sen' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:200'],
        ], [
            'amount_sen.required' => 'Enter how much you refunded.',
            'amount_sen.min' => 'Enter how much you refunded.',
            'reason.required' => 'Say why, e.g. paid after the time ran out.',
        ]);

        $refunded = DB::transaction(function () use ($request, $venue, $payment, $data) {
            // The row lock: two owners recording at once are counted one after the other.
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, [PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
                throw ValidationException::withMessages(['amount_sen' => 'Only money that was paid can be refunded.']);
            }
            $left = $locked->refundableSen();
            if ($data['amount_sen'] > $left) {
                throw ValidationException::withMessages([
                    'amount_sen' => $left === 0 ? 'This payment is already fully refunded.' : 'You can record at most '.Money::format($left).' (what was paid, minus earlier refunds).',
                ]);
            }

            $refund = new Refund;
            $refund->forceFill([
                'payment_id' => $locked->id,
                'venue_id' => $venue->id,
                'amount_sen' => $data['amount_sen'],
                'reason' => trim($data['reason']),
                'recorded_by' => $request->user()->id,
            ])->save();

            $locked->refunded_sen += $data['amount_sen'];
            if ($locked->refundableSen() === 0) {
                $locked->status = PaymentStatus::Refunded;
            }
            $locked->save();

            AuditLog::record(Booking::findOrFail($locked->booking_id), 'payment.refunded', $request->user(), [
                'payment_id' => $locked->id,
                'amount_sen' => $refund->amount_sen,
                'reason' => $refund->reason,
            ]);

            return $locked;
        });

        return new ManagePaymentResource($refunded->load(['booking.court', 'booking.user']));
    }
}
