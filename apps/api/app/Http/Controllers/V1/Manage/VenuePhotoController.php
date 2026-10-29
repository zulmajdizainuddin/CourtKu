<?php

namespace App\Http\Controllers\V1\Manage;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Resources\VenuePhotoResource;
use App\Models\Venue;
use App\Models\VenuePhoto;
use App\Services\Cloudinary;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

/**
 * A venue's photos (FR-06), stored on Cloudinary (ADR-018). Owners add, reorder and
 * remove them; staff can see them. The first photo is the venue's cover photo.
 */
class VenuePhotoController extends Controller
{
    /** Enough for a good gallery, and well inside Cloudinary's free plan. */
    private const MAX_PHOTOS = 10;

    public function __construct(private readonly Cloudinary $cloudinary) {}

    /** GET /api/v1/manage/venues/{venue}/photos: in the owner's chosen order. */
    public function index(Venue $venue): AnonymousResourceCollection
    {
        return VenuePhotoResource::collection($venue->photos()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** POST /api/v1/manage/venues/{venue}/photos: the new photo goes at the end. */
    public function store(StorePhotoRequest $request, Venue $venue): JsonResponse
    {
        if ($venue->photos()->count() >= self::MAX_PHOTOS) {
            throw new ApiException('PHOTO_LIMIT', 'A venue can have at most '.self::MAX_PHOTOS.' photos. Remove one first.');
        }

        // Upload first: if Cloudinary fails, nothing is saved here.
        /** @var UploadedFile $file */
        $file = $request->file('photo');
        $stored = $this->cloudinary->upload($file, "venues/{$venue->id}");

        $photo = $venue->photos()->create([
            ...$stored,
            'sort_order' => (int) $venue->photos()->max('sort_order') + 1,
        ]);

        return (new VenuePhotoResource($photo))->response()->setStatusCode(201);
    }

    /** DELETE /api/v1/manage/venues/{venue}/photos/{photo} */
    public function destroy(Venue $venue, VenuePhoto $photo): Response
    {
        $photo->delete();
        $this->cloudinary->destroy($photo->public_id);

        return response()->noContent();
    }

    /** PUT /api/v1/manage/venues/{venue}/photos/order  { "photo_ids": [5, 2, 3] } */
    public function reorder(Request $request, Venue $venue): AnonymousResourceCollection
    {
        $data = $request->validate([
            'photo_ids' => ['required', 'array'],
            'photo_ids.*' => ['integer', 'distinct'],
        ]);

        SortOrder::apply($venue->photos(), array_map(intval(...), $data['photo_ids']), 'photo_ids');

        return $this->index($venue);
    }
}
