<?php

namespace App\Http\Requests;

use App\Enums\VenueStatus;
use App\Models\Venue;
use App\Services\AvailabilityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * GET /api/v1/venues/{slug}/availability?date=2026-11-06&sport=badminton
 * The sport is optional: without it, every bookable court is shown.
 */
class AvailabilityRequest extends FormRequest
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
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'sport' => ['nullable', 'string', Rule::exists('sports', 'slug')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Choose a date.',
            'date.date_format' => 'Choose a date like 2026-11-06.',
            'sport.exists' => 'Choose badminton, futsal or pickleball.',
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
                $availability = new AvailabilityService($this->venue());
                if (! $availability->inWindow(AvailabilityService::date($this->string('date')->value()))) {
                    $validator->errors()->add('date', sprintf(
                        'Choose a date from today to %s.',
                        $availability->lastDate()->format('j M Y'),
                    ));
                }
            },
        ];
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
