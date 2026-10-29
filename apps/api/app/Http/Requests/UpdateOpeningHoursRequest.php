<?php

namespace App\Http\Requests;

use App\Support\OpeningHoursRules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * PUT /api/v1/manage/venues/{venue}/hours: the whole week at once (FR-07).
 * { "periods": [ { "weekday": 5, "opens_at": "08:00", "closes_at": "12:30" }, ... ] }
 * An empty list means closed every day. A day with no periods is closed that day.
 */
class UpdateOpeningHoursRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // On the hour or half hour, because bookings use 30-minute cells (ADR-010).
        $time = ['required', 'string', 'regex:/^([01]\d|2[0-3]):(00|30)$/'];

        return [
            'periods' => ['present', 'array', 'max:14'],
            'periods.*' => ['array'],
            'periods.*.weekday' => ['required', 'integer', 'between:0,6'],
            'periods.*.opens_at' => $time,
            'periods.*.closes_at' => $time,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $halfHour = 'Use a time on the hour or half hour, e.g. 08:00 or 08:30.';

        return [
            'periods.*.opens_at.regex' => $halfHour,
            'periods.*.closes_at.regex' => $halfHour,
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // The rules above must pass first: the overlap check needs real times.
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                foreach (OpeningHoursRules::problems($this->periodsInput()) as $field => $message) {
                    $validator->errors()->add($field, $message);
                }
            },
        ];
    }

    /**
     * The periods ready to save, with closes_next_day worked out from the times.
     *
     * @return list<array{weekday: int, opens_at: string, closes_at: string, closes_next_day: bool}>
     */
    public function periods(): array
    {
        return array_map(fn (array $period) => [
            ...$period,
            'closes_next_day' => OpeningHoursRules::closesNextDay($period['opens_at'], $period['closes_at']),
        ], $this->periodsInput());
    }

    /**
     * @return list<array{weekday: int, opens_at: string, closes_at: string}>
     */
    private function periodsInput(): array
    {
        /** @var list<array{weekday: int|string, opens_at: string, closes_at: string}> $periods */
        $periods = array_values($this->input('periods', []));

        return array_map(fn (array $p) => [
            'weekday' => (int) $p['weekday'],
            'opens_at' => $p['opens_at'],
            'closes_at' => $p['closes_at'],
        ], $periods);
    }
}
