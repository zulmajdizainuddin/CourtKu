<?php

namespace App\Http\Requests;

use App\Enums\VenueStatus;
use App\Models\Venue;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * POST /api/v1/venues/{slug}/quote: what one booking would cost (FR-09, FR-10).
 * { "court_id": 6, "starts_at": "2026-11-06T18:00:00+08:00", "minutes": 120 }
 * starts_at is the real time, so Saturday 00:30 after a late Friday is "2026-11-07T00:30:00+08:00".
 * A time without an offset is read as Malaysia time.
 */
class QuoteRequest extends FormRequest
{
    /** Only approved venues answer; any other is 404 before its input is even checked. */
    protected function prepareForValidation(): void
    {
        abort_unless($this->venue()->status === VenueStatus::Active, 404);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $venue = $this->venue();

        return [
            'court_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            // The venue's shortest and longest booking, in whole slots (FR-13).
            'minutes' => ['required', 'integer', "between:{$venue->min_minutes},{$venue->max_minutes}", "multiple_of:{$venue->slot_minutes}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $venue = $this->venue();
        $lengths = "Bookings here are {$venue->min_minutes} to {$venue->max_minutes} minutes long, in {$venue->slot_minutes}-minute steps.";

        return [
            'minutes.between' => $lengths,
            'minutes.multiple_of' => $lengths,
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('starts_at')) {
                    return;
                }
                // Bookings start on the hour or half hour (ADR-010).
                $startsAt = $this->startsAt();
                if ($startsAt->second !== 0 || $startsAt->minute % 30 !== 0) {
                    $validator->errors()->add('starts_at', 'Choose a time on the hour or half hour.');
                }
            },
        ];
    }

    /** The start time, in Malaysia time. */
    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('starts_at')->value(), MalaysiaTime::TIMEZONE)
            ->setTimezone(MalaysiaTime::TIMEZONE);
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
