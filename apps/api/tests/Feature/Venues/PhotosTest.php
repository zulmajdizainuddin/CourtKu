<?php

use App\Models\User;
use App\Models\Venue;
use App\Services\Cloudinary;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/** An owner (with two-factor) and their venue. */
function photosOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

beforeEach(function () {
    config(['services.cloudinary' => [
        'cloud_name' => 'courtku-test',
        'api_key' => '1234',
        'api_secret' => 'secret',
        'folder' => 'courtku-tests',
    ]]);

    // Never talk to the real Cloudinary in tests; answer like it would.
    Http::fake([
        'api.cloudinary.com/v1_1/courtku-test/image/upload' => Http::response([
            'public_id' => 'courtku-tests/venues/1/x8sd0fk2', 'width' => 1600, 'height' => 1200,
        ]),
        'api.cloudinary.com/v1_1/courtku-test/image/destroy' => Http::response(['result' => 'ok']),
    ]);
});

it('FR-06 uploads a photo to Cloudinary, signed, into the venue\'s folder', function () {
    [$owner, $venue] = photosOwnerAndVenue();

    $this->actingAs($owner)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => UploadedFile::fake()->image('court.jpg', 1600, 1200)], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.width', 1600)
        ->assertJsonPath('data.thumbnail_url', 'https://res.cloudinary.com/courtku-test/image/upload/c_fill,w_400,h_300,f_auto,q_auto/courtku-tests/venues/1/x8sd0fk2');

    Http::assertSent(function (Request $request) use ($venue) {
        $fields = collect($request->data())->pluck('contents', 'name');

        return str_ends_with($request->url(), '/image/upload')
            && $fields['folder'] === "courtku-tests/venues/{$venue->id}"
            && $fields['transformation'] === 'c_limit,w_2000,h_2000' // re-encoded, metadata removed
            && $fields['api_key'] === '1234'
            && strlen((string) $fields['signature']) === 40
            && ! $fields->has('api_secret'); // the secret never leaves our server
    });
    expect($venue->photos()->sole()->public_id)->toBe('courtku-tests/venues/1/x8sd0fk2');
});

it('only accepts real photos of a sensible size', function (UploadedFile $file) {
    [$owner, $venue] = photosOwnerAndVenue();

    $this->actingAs($owner)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => $file], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('photo', 'error.details.fields');

    Http::assertNothingSent();
    expect($venue->photos()->count())->toBe(0);
})->with([
    'a PDF' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'an SVG, which can hide scripts' => fn () => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
    'too small' => fn () => UploadedFile::fake()->image('tiny.jpg', 200, 150),
    'over 4 MB' => fn () => UploadedFile::fake()->image('huge.jpg', 1600, 1200)->size(5000),
]);

it('saves nothing when Cloudinary fails', function () {
    [$owner, $venue] = photosOwnerAndVenue();
    // A fresh fake: the "upload OK" answer from beforeEach would otherwise be used first.
    Http::swap(new Factory);
    Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Down']], 500)]);

    $this->actingAs($owner)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => UploadedFile::fake()->image('court.jpg', 800, 600)], ['Accept' => 'application/json'])
        ->assertStatus(502)
        ->assertJsonPath('error.code', 'PHOTO_UPLOAD_FAILED');

    expect($venue->photos()->count())->toBe(0);
});

it('says so clearly when photo storage is not set up on the server', function () {
    config(['services.cloudinary.api_secret' => null]);
    app()->forgetInstance(Cloudinary::class);
    [$owner, $venue] = photosOwnerAndVenue();

    $this->actingAs($owner)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => UploadedFile::fake()->image('court.jpg', 800, 600)], ['Accept' => 'application/json'])
        ->assertStatus(503)
        ->assertJsonPath('error.code', 'PHOTO_STORAGE_OFF');
});

it('stops at 10 photos per venue', function () {
    [$owner, $venue] = photosOwnerAndVenue();
    foreach (range(1, 10) as $i) {
        $venue->photos()->create(['public_id' => "p{$i}", 'width' => 800, 'height' => 600]);
    }

    $this->actingAs($owner)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => UploadedFile::fake()->image('court.jpg', 800, 600)], ['Accept' => 'application/json'])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'PHOTO_LIMIT');

    Http::assertNothingSent();
});

it('removes a photo, also from Cloudinary', function () {
    [$owner, $venue] = photosOwnerAndVenue();
    $photo = $venue->photos()->create(['public_id' => 'courtku-tests/venues/1/old', 'width' => 800, 'height' => 600]);

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/photos/{$photo->id}")->assertNoContent();

    expect($venue->photos()->count())->toBe(0);
    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/image/destroy')
        && $request['public_id'] === 'courtku-tests/venues/1/old');
});

it('puts the photos in the owner\'s order; the first is the cover photo', function () {
    [$owner, $venue] = photosOwnerAndVenue();
    $first = $venue->photos()->create(['public_id' => 'a', 'width' => 800, 'height' => 600, 'sort_order' => 0]);
    $second = $venue->photos()->create(['public_id' => 'b', 'width' => 800, 'height' => 600, 'sort_order' => 1]);

    $this->actingAs($owner)
        ->putJson("/api/v1/manage/venues/{$venue->id}/photos/order", ['photo_ids' => [$second->id, $first->id]])
        ->assertOk()
        ->assertJsonPath('data.0.id', $second->id);
});

it('lets staff see the photos but not change them', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();
    $photo = $venue->photos()->create(['public_id' => 'a', 'width' => 800, 'height' => 600]);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/photos")->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($staff)
        ->post("/api/v1/manage/venues/{$venue->id}/photos", ['photo' => UploadedFile::fake()->image('court.jpg', 800, 600)], ['Accept' => 'application/json'])
        ->assertForbidden();
    $this->actingAs($staff)->deleteJson("/api/v1/manage/venues/{$venue->id}/photos/{$photo->id}")->assertForbidden();

    Http::assertNothingSent();
});
