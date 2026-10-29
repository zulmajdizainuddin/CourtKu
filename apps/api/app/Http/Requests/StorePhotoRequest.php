<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/manage/venues/{venue}/photos with one image in the "photo" field (FR-06).
 * Plan section 11: images only, limited in size, stored under a random name.
 */
class StorePhotoRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // JPEG, PNG or WebP only (no SVG, which can hide scripts), at most 4 MB
            // (the web app shrinks photos first; Vercel's proxy refuses bodies over 4.5 MB).
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:4096', 'dimensions:min_width=400,min_height=300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.image' => 'Choose a photo (JPEG, PNG or WebP).',
            'photo.mimes' => 'Choose a photo (JPEG, PNG or WebP).',
            'photo.max' => 'This photo is too big. Use one under 4 MB.',
            'photo.dimensions' => 'This photo is too small. Use one at least 400 × 300 pixels.',
        ];
    }
}
