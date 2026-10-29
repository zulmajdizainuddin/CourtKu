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
 * { "name": "Court 1", "is_indoor": true, "is_active": true, "sports": ["badminton", "futsal"] }
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
        ];
    }

    /**
     * The court's own fields, ready to save.
     *
     * @return array{name: string, is_indoor: bool, is_active?: bool}
     */
    public function courtData(): array
    {
        /** @var array{name: string, is_indoor: bool, is_active?: bool} */
        return $this->safe()->only(['name', 'is_indoor', 'is_active']);
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
