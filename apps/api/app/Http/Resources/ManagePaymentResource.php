<?php

namespace App\Http\Resources;

use App\Enums\BookingStatus;
use App\Models\Payment;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An online payment as the venue's owner sees it, e.g. one that needs a refund (FR-25):
 * which booking, who paid, how much, and what went wrong.
 *
 * @mixin Payment
 */
class ManagePaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $booking = $this->booking;

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'problem' => $this->problem?->value,
            // Why the owner should refund it, in one sentence.
            'problem_text' => $this->problem?->describe()
                ?? ($booking->status === BookingStatus::Cancelled ? 'The booking was cancelled after it was paid online.' : null),
            'amount_sen' => $this->amount_sen,
            'paid_amount_sen' => $this->paid_amount_sen,
            'paid_amount' => $this->paid_amount_sen === null ? null : Money::format($this->paid_amount_sen),
            // Refunds recorded so far (FR-25), and what can still be refunded.
            'refunded_sen' => $this->refunded_sen,
            'refunded' => Money::format($this->refunded_sen),
            'refundable_sen' => $this->refundableSen(),
            'paid_at' => $this->paid_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            // The payment in the venue's own Stripe dashboard (search for it there).
            'checkout_id' => $this->checkout_id,
            'booking' => [
                'code' => $booking->code,
                'status' => $booking->status->value,
                'court' => $booking->court->name,
                'starts_at' => $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
                'customer' => [
                    'name' => $booking->user->name ?? $booking->customer_name,
                    'email' => $booking->user?->email,
                    'phone' => $booking->user->phone ?? $booking->customer_phone,
                ],
            ],
        ];
    }
}
