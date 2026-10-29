<?php

namespace App\Http\Requests;

use App\Models\Venue;
use App\Support\MalaysiaTime;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

/**
 * POST /api/v1/manage/venues/{venue}/closures: closed from one date to another (FR-07),
 * e.g. { "starts_on": "2027-03-30", "ends_on": "2027-04-01", "reason": "Hari Raya" }.
 */
class StoreClosureRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'starts_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.MalaysiaTime::today()],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'reason' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'starts_on.after_or_equal' => 'Choose today or a later date.',
            'ends_on.after_or_equal' => 'The last day can\'t be before the first day.',
            'reason.required' => 'Tell players why, e.g. Hari Raya or renovation.',
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

                $startsOn = Carbon::parse($this->string('starts_on'));
                $endsOn = Carbon::parse($this->string('ends_on'));

                if ($startsOn->diffInDays($endsOn) >= 366) {
                    $validator->errors()->add('ends_on', 'A closure can be at most a year long.');

                    return;
                }

                // Two closures on the same days would be confusing to show and to remove.
                $overlap = $this->venue()->closures()
                    ->where('starts_on', '<=', $endsOn->toDateString())
                    ->where('ends_on', '>=', $startsOn->toDateString())
                    ->first();

                if ($overlap !== null) {
                    $validator->errors()->add('starts_on', sprintf(
                        'These days overlap a closure you already added: %s (%s to %s).',
                        $overlap->reason,
                        $overlap->starts_on->format('j M Y'),
                        $overlap->ends_on->format('j M Y'),
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
