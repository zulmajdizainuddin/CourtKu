<?php

namespace App\Http\Requests;

use App\Enums\MalaysianState;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/v1/manage/venues: the first step of listing a venue (FR-04).
 * Only the basics; the full profile is filled in afterwards.
 */
class StoreVenueRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            // Lowercase letters and numbers, joined by single hyphens: arena-sg-buloh.
            'slug' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('venues', 'slug')],
            'state' => ['required', Rule::enum(MalaysianState::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use lowercase letters, numbers and hyphens only, e.g. arena-sg-buloh.',
            'slug.unique' => 'Another venue already uses this web address. Try a different one.',
        ];
    }
}
