<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * PUT /api/v1/manage/venues/{venue}/settings: how players may book (FR-13).
 * { "slot_minutes": 60, "min_minutes": 60, "max_minutes": 180, "booking_window_days": 14, "min_notice_minutes": 0 }
 */
class UpdateBookingSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 30- or 60-minute slots; bookings are always stored in 30-minute cells (ADR-010).
            'slot_minutes' => ['required', 'integer', 'in:30,60'],
            'min_minutes' => ['required', 'integer', 'min:30', 'max:720'],
            'max_minutes' => ['required', 'integer', 'min:30', 'max:720', 'gte:min_minutes'],
            'booking_window_days' => ['required', 'integer', 'min:1', 'max:90'],
            // How close to the start players may still book: right up to it (0), or 30 minutes,
            // 1 hour, 2 hours or 1 day before.
            'min_notice_minutes' => ['required', 'integer', 'in:0,30,60,120,1440'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slot_minutes.in' => 'Choose 30- or 60-minute slots.',
            'max_minutes.gte' => 'The longest booking can\'t be shorter than the shortest.',
            'max_minutes.max' => 'The longest booking can be at most 12 hours.',
            'booking_window_days.max' => 'Players can book at most 90 days ahead.',
            'min_notice_minutes.in' => 'Choose until the start, or 30 minutes, 1 hour, 2 hours or 1 day before.',
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                // A booking is a whole number of slots: with 60-minute slots, 90 minutes is impossible.
                $slot = $this->integer('slot_minutes');
                foreach (['min_minutes' => 'shortest', 'max_minutes' => 'longest'] as $field => $word) {
                    if ($this->integer($field) % $slot !== 0) {
                        $validator->errors()->add($field, "The {$word} booking must be a whole number of {$slot}-minute slots.");
                    }
                }
            },
        ];
    }
}
