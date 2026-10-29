<?php

namespace App\Http\Requests;

use App\Enums\Facility;
use App\Enums\MalaysianState;
use App\Enums\VenueStatus;
use App\Models\Venue;
use App\Support\MalaysianPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * PUT /api/v1/manage/venues/{venue}: the owner saves the venue profile (FR-06).
 * The whole profile is sent each time; empty optional fields are saved as empty.
 */
class UpdateVenueRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|Closure|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'slug' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('venues', 'slug')->ignore($this->venue()->id)],
            'state' => ['required', Rule::enum(MalaysianState::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'regex:/^\d{5}$/'],
            'phone' => ['nullable', 'string', 'max:20', function (string $attribute, mixed $value, Closure $fail) {
                if (! is_string($value) || MalaysianPhone::normalize($value) === null) {
                    $fail('Enter a Malaysian phone number, e.g. 012-345 6789 or 03-1234 5678.');
                }
            }],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['distinct', Rule::enum(Facility::class)],
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
            'postcode.regex' => 'Use the 5-digit postcode, e.g. 47000.',
        ];
    }

    /**
     * Rules that need the venue itself, checked after the field rules above.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Once players can see the venue, its address may be saved or shared,
                // so changing it would break those links. Typos can be fixed before approval.
                $venue = $this->venue();
                if ($this->input('slug') !== $venue->slug && $venue->status !== VenueStatus::Pending) {
                    $validator->errors()->add('slug', 'The web address is locked once the venue is approved, so links players saved keep working.');
                }
            },
        ];
    }

    /**
     * The validated profile, ready to save: the phone in +60 format.
     *
     * @return array<string, mixed>
     */
    public function venueData(): array
    {
        $data = $this->validated();
        $data['phone'] = isset($data['phone']) ? MalaysianPhone::normalize($data['phone']) : null;
        $data['facilities'] = array_values($data['facilities'] ?? []);

        return $data;
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
