<?php

namespace App\Http\Resources;

use App\Models\VenuePhoto;
use App\Services\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A venue photo, with two ready-made addresses from Cloudinary:
 * a small one for lists and galleries, and a large one to view it full size.
 *
 * @mixin VenuePhoto
 */
class VenuePhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cloudinary = app(Cloudinary::class);

        return [
            'id' => $this->id,
            'thumbnail_url' => $cloudinary->url($this->public_id, 'c_fill,w_400,h_300'),
            'url' => $cloudinary->url($this->public_id, 'c_limit,w_1600'),
            'width' => $this->width,
            'height' => $this->height,
            'sort_order' => $this->sort_order,
        ];
    }
}
