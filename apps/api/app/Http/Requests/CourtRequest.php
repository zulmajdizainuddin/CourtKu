<?php

namespace App\Http\Requests;

use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Adding (POST) or changing (PUT) a court (FR-08):
 * { "name": "Court 1", "is_indoor": true, "base_price_sen": 2500, "is_active": true, "sports": ["badminton"] }
 */
class CourtRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $court = $this->route('court');

        return [
            // Unique within this venue only: every venue may have its own "Court 1".
            'name' => ['required', 'string', 'max:50', Rule::unique('courts', 'name')
                ->where('venue_id', $this->venue()->id)
                ->ignore($court instanceof Court ? $court->id : null)],
            'is_indoor' => ['required', 'boolean'],
            // FR-09: price per hour in sen, at most RM 1,000.00. Multiples of 10 sen (ADR-017),
            // so half an hour is always a whole number of sen. null = not bookable yet; left out = unchanged.
            'base_price_sen' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000', 'multiple_of:10'],
            'is_active' => ['sometimes', 'boolean'],
            'sports' => ['required', 'array', 'min:1'],
            'sports.*' => ['distinct', Rule::exists('sports', 'slug')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'This venue already has a court with this name.',
            'sports.required' => 'Choose at least one sport.',
            'sports.min' => 'Choose at least one sport.',
            'base_price_sen.multiple_of' => 'Use a price in 10-sen steps, e.g. RM 25.00 or RM 25.50.',
            'base_price_sen.max' => 'The price can be at most RM 1,000.00 an hour.',
        ];
    }

    /**
     * The court's own fields, ready to save.
     *
     * @return array{name: string, is_indoor: bool, base_price_sen?: int|null, is_active?: bool}
     */
    public function courtData(): array
    {
        /** @var array{name: string, is_indoor: bool, base_price_sen?: int|null, is_active?: bool} */
        return $this->safe()->only(['name', 'is_indoor', 'base_price_sen', 'is_active']);
    }

    /**
     * The ids of the chosen sports, for the court_sports link table.
     *
     * @return list<int>
     */
    public function sportIds(): array
    {
        /** @var list<int> */
        return Sport::whereIn('slug', $this->validated('sports'))->orderBy('id')->pluck('id')->all();
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
