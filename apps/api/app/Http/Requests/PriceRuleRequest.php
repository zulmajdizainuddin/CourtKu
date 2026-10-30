<?php

namespace App\Http\Requests;

use App\Models\PriceRule;
use App\Models\Venue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Adding (POST) or changing (PUT) a peak or off-peak price (FR-10):
 * { "name": "Weekday peak", "court_id": null, "weekdays": [1,2,3,4,5],
 *   "starts_at": "18:00", "ends_at": "00:00", "price_sen": 3000 }
 * court_id null = all courts. An end time before the start time means the next day.
 */
class PriceRuleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // On the hour or half hour, because bookings use 30-minute cells (ADR-010).
        $time = ['required', 'string', 'regex:/^([01]\d|2[0-3]):(00|30)$/'];

        return [
            'name' => ['nullable', 'string', 'max:50'],
            // Only a court of this venue: venue A can't put a price on venue B's court.
            'court_id' => ['nullable', 'integer', Rule::exists('courts', 'id')->where('venue_id', $this->venue()->id)],
            'weekdays' => ['required', 'array', 'min:1', 'max:7'],
            'weekdays.*' => ['integer', 'between:0,6', 'distinct'],
            'starts_at' => $time,
            'ends_at' => [...$time, 'different:starts_at'],
            // Per hour, in 10-sen steps like the court's normal price (ADR-017).
            'price_sen' => ['required', 'integer', 'min:0', 'max:100000', 'multiple_of:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $halfHour = 'Use a time on the hour or half hour, e.g. 18:00 or 18:30.';

        return [
            'court_id.exists' => 'Choose one of this venue\'s courts.',
            'weekdays.required' => 'Choose at least one day.',
            'weekdays.min' => 'Choose at least one day.',
            'starts_at.regex' => $halfHour,
            'ends_at.regex' => $halfHour,
            'ends_at.different' => 'The end time must be different from the start time.',
            'price_sen.multiple_of' => 'Use a price in 10-sen steps, e.g. RM 30.00 or RM 30.50.',
            'price_sen.max' => 'The price can be at most RM 1,000.00 an hour.',
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // The rules above must pass first: the clash check needs real days and times.
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $rule = new PriceRule($this->ruleData());
                $current = $this->route('priceRule');

                // ADR-020: no two rules for the same courts on the same half hour.
                $clash = $this->venue()->priceRules()
                    ->when($current instanceof PriceRule, fn ($query) => $query->whereKeyNot($current->id))
                    ->get()
                    ->first(fn (PriceRule $other) => $rule->clashesWith($other));

                if ($clash !== null) {
                    $validator->errors()->add('starts_at', sprintf(
                        'This overlaps %s for %s. Change the days or times so each half hour has one price.',
                        $clash->describe(),
                        $clash->court_id === null ? 'all courts' : 'the same court',
                    ));
                }
            },
        ];
    }

    /**
     * The rule's fields, ready to save.
     *
     * @return array{name: string|null, court_id: int|null, weekdays: list<int>, starts_at: string, ends_at: string, ends_next_day: bool, price_sen: int}
     */
    public function ruleData(): array
    {
        $weekdays = array_values(array_map(intval(...), $this->array('weekdays')));
        sort($weekdays);
        $startsAt = $this->string('starts_at')->value();
        $endsAt = $this->string('ends_at')->value();

        return [
            'name' => $this->filled('name') ? $this->string('name')->trim()->value() : null,
            'court_id' => $this->filled('court_id') ? $this->integer('court_id') : null,
            'weekdays' => $weekdays,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            // 18:00 to 02:00 ends the next morning, 18:00 to 00:00 ends at midnight.
            'ends_next_day' => PriceRule::minutes($endsAt) < PriceRule::minutes($startsAt),
            'price_sen' => $this->integer('price_sen'),
        ];
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
