<?php

namespace App\Http\Requests;

use App\Enums\PaymentMode;
use App\Models\Venue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * PUT /api/v1/manage/venues/{venue}/payment-settings  { "payment_mode": "both", "hold_minutes": 15 }
 * How players pay (FR-17), and how long they have to pay online before the time is freed (FR-16).
 */
class UpdatePaymentSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_mode' => ['required', Rule::enum(PaymentMode::class)],
            'hold_minutes' => ['required', 'integer', 'in:10,15,20,30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_mode.required' => 'Choose how players pay.',
            'payment_mode.enum' => 'Choose at the counter, online, or both.',
            'hold_minutes.in' => 'Choose 10, 15, 20 or 30 minutes.',
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var Venue $venue */
                $venue = $this->route('venue');
                $mode = PaymentMode::tryFrom($this->string('payment_mode')->value());
                // Online payment goes to the venue's own account (ADR-009), so it needs one that Stripe says is ready.
                if ($mode?->needsAccount() && ! $venue->paymentAccount?->isReady()) {
                    $validator->errors()->add('payment_mode', 'Connect your Stripe account first, then players can pay online.');
                }
            },
        ];
    }
}
