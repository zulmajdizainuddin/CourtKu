<?php

namespace App\Http\Requests;

use App\Support\MalaysianPhone;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/v1/manage/venues/{venue}/bookings: staff book for a customer at the counter or on the
 * phone (FR-20). { "court_id": 6, "sport": "badminton", "starts_at": "2026-11-06T18:00:00+08:00",
 * "minutes": 60, "source": "walk_in", "customer_name": "Ali", "customer_phone": "012-345 6789" }
 * The time follows the same rules as players' bookings (BookingService::prepare).
 */
class StoreCounterBookingRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string|Closure>
     */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer'],
            'sport' => ['required', 'string', Rule::exists('sports', 'slug')],
            'starts_at' => ['required', 'date'],
            'minutes' => ['required', 'integer', 'min:30', 'max:720'],
            'source' => ['required', 'in:walk_in,phone'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail) {
                if (! is_string($value) || MalaysianPhone::normalize($value) === null) {
                    $fail('Enter a Malaysian phone number, e.g. 012-345 6789.');
                }
            }],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Enter the customer\'s name.',
            'customer_phone.required' => 'Enter the customer\'s phone number.',
            'source.in' => 'Choose walk-in or phone.',
            'sport.required' => 'Choose a sport.',
        ];
    }

    /** The start time, in Malaysia time. */
    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('starts_at')->value(), MalaysiaTime::TIMEZONE)
            ->setTimezone(MalaysiaTime::TIMEZONE);
    }

    /** "012-345 6789" -> "+60123456789", the one format CourtKu stores. */
    public function customerPhone(): string
    {
        return (string) MalaysianPhone::normalize($this->string('customer_phone')->value());
    }
}
