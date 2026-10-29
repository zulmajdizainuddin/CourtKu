<?php

use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Models\User;
use App\Models\Venue;

$validVenue = fn (array $overrides = []) => array_merge([
    'name' => 'Arena Sungai Buloh',
    'slug' => 'arena-sg-buloh',
    'state' => 'selangor',
], $overrides);

it('FR-04 lists a new venue as pending, with the user as its owner', function () use ($validVenue) {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)->postJson('/api/v1/manage/venues', $validVenue())
        ->assertCreated()
        ->assertJsonPath('data.name', 'Arena Sungai Buloh')
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.role', 'owner');

    $venue = Venue::sole();
    expect($venue->status)->toBe(VenueStatus::Pending)
        ->and($venue->roleOf($user))->toBe(VenueRole::Owner);
});

it('FR-04 ignores a status sent by the owner: only an admin can make a venue active', function () use ($validVenue) {
    $this->actingAs(User::factory()->withTwoFactor()->create())
        ->postJson('/api/v1/manage/venues', $validVenue(['status' => 'active', 'is_demo' => true]))
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');

    expect(Venue::sole()->is_demo)->toBeFalse();
});

it('requires a logged-in user with a verified email', function () use ($validVenue) {
    $this->postJson('/api/v1/manage/venues', $validVenue())->assertUnauthorized();

    $this->actingAs(User::factory()->unverified()->create())
        ->postJson('/api/v1/manage/venues', $validVenue())
        ->assertForbidden();

    expect(Venue::count())->toBe(0);
});

it('checks the name, web address and state', function (array $input, string $field) use ($validVenue) {
    $this->actingAs(User::factory()->withTwoFactor()->create())
        ->postJson('/api/v1/manage/venues', $validVenue($input))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED')
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    'name too short' => [['name' => 'AB'], 'name'],
    'slug with capitals' => [['slug' => 'Arena-SG'], 'slug'],
    'slug with spaces' => [['slug' => 'arena sg'], 'slug'],
    'slug ending in a hyphen' => [['slug' => 'arena-'], 'slug'],
    'slug with double hyphens' => [['slug' => 'arena--sg'], 'slug'],
    'unknown state' => [['state' => 'singapore'], 'state'],
]);

it('refuses a web address another venue already uses', function () use ($validVenue) {
    Venue::factory()->create(['slug' => 'arena-sg-buloh']);

    $this->actingAs(User::factory()->withTwoFactor()->create())
        ->postJson('/api/v1/manage/venues', $validVenue())
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.slug.0', 'Another venue already uses this web address. Try a different one.');
});

it('allows only one venue waiting for approval per owner', function () use ($validVenue) {
    $owner = User::factory()->withTwoFactor()->create();
    Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->postJson('/api/v1/manage/venues', $validVenue())
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'VENUE_PENDING_EXISTS');
});

it('lets an owner list another venue once the first is approved', function () use ($validVenue) {
    $owner = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($owner)->create();

    $this->actingAs($owner)->postJson('/api/v1/manage/venues', $validVenue())->assertCreated();
});
