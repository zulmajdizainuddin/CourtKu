<?php

use App\Enums\VenueStatus;
use App\Models\User;
use App\Models\Venue;

$profile = fn (Venue $venue, array $overrides = []) => array_merge([
    'name' => 'Arena Sungai Buloh',
    'slug' => $venue->slug,
    'state' => 'selangor',
    'description' => '6 badminton courts with wooden floors.',
    'address_line' => 'Lot 12, Jalan Industri 3',
    'city' => 'Sungai Buloh',
    'postcode' => '47000',
    'phone' => '012-345 6789',
    'facilities' => ['parking', 'surau', 'showers'],
], $overrides);

function ownerWithVenue(array $venueState = []): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create($venueState);

    return [$owner, $venue];
}

it('FR-06 saves the venue profile, with the phone in +60 format', function () use ($profile) {
    [$owner, $venue] = ownerWithVenue();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue))
        ->assertOk()
        ->assertJsonPath('data.city', 'Sungai Buloh')
        ->assertJsonPath('data.postcode', '47000')
        ->assertJsonPath('data.phone', '+60123456789')
        ->assertJsonPath('data.facilities', ['parking', 'surau', 'showers']);

    expect($venue->refresh()->address_line)->toBe('Lot 12, Jalan Industri 3');
});

it('clears optional fields the owner left empty', function () use ($profile) {
    [$owner, $venue] = ownerWithVenue(['city' => 'Shah Alam', 'phone' => '+60312345678', 'facilities' => ['wifi']]);

    $this->actingAs($owner)
        ->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, ['city' => null, 'phone' => null, 'facilities' => []]))
        ->assertOk()
        ->assertJsonPath('data.city', null)
        ->assertJsonPath('data.phone', null)
        ->assertJsonPath('data.facilities', []);
});

it('checks every field', function (array $input, string $field) use ($profile) {
    [$owner, $venue] = ownerWithVenue();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, $input))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    'postcode with 4 digits' => [['postcode' => '4700'], 'postcode'],
    'postcode with letters' => [['postcode' => 'AB123'], 'postcode'],
    'foreign phone number' => [['phone' => '+65 9123 4567'], 'phone'],
    'unknown facility' => [['facilities' => ['helipad']], 'facilities.0'],
    'facility twice' => [['facilities' => ['wifi', 'wifi']], 'facilities.0'],
    'description too long' => [['description' => str_repeat('a', 2001)], 'description'],
    'no name' => [['name' => ''], 'name'],
]);

it('lets the owner fix the web address while the venue is pending', function () use ($profile) {
    [$owner, $venue] = ownerWithVenue(['slug' => 'arena-sg-bulohh']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, ['slug' => 'arena-sg-buloh']))
        ->assertOk()
        ->assertJsonPath('data.slug', 'arena-sg-buloh');
});

it('locks the web address once the venue is approved', function () use ($profile) {
    [$owner, $venue] = ownerWithVenue(['slug' => 'arena-sg-buloh', 'status' => VenueStatus::Active]);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, ['slug' => 'new-name']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('slug', 'error.details.fields');

    // Saving with the same address still works.
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue))->assertOk();

    expect($venue->refresh()->slug)->toBe('arena-sg-buloh');
});

it('refuses a web address another venue already uses', function () use ($profile) {
    Venue::factory()->create(['slug' => 'taken']);
    [$owner, $venue] = ownerWithVenue();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, ['slug' => 'taken']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('slug', 'error.details.fields');
});

it('never lets the profile form change the status', function () use ($profile) {
    [$owner, $venue] = ownerWithVenue();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue, ['status' => 'active']))
        ->assertOk()
        ->assertJsonPath('data.status', 'pending');
});

it('lets staff see the profile but not change it', function () use ($profile) {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create(['name' => 'Old Name']);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}")->assertOk();

    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}", $profile($venue))
        ->assertForbidden()
        ->assertJsonPath('error.message', 'Only the venue owner can change this.');

    expect($venue->refresh()->name)->toBe('Old Name');
});
