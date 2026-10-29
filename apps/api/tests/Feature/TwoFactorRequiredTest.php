<?php

use App\Models\User;
use App\Models\Venue;

/*
| Owners and the platform admin must use two-factor login (plan section 11).
| Without it they get 403 TWO_FACTOR_REQUIRED, and the web app sends them to set it up.
*/

it('stops an admin without two-factor', function () {
    $admin = User::factory()->create()->forceFill(['is_platform_admin' => true]);
    $venue = Venue::factory()->create();

    $this->actingAs($admin)->getJson('/api/v1/admin/venues')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');

    $this->actingAs($admin)->postJson("/api/v1/admin/venues/{$venue->id}/approve")
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');
});

it('stops an owner without two-factor, even before the venue is approved', function () {
    $owner = User::factory()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->getJson('/api/v1/manage/venues')
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');
});

it('asks for two-factor before anyone lists a venue, since that makes them an owner', function () {
    $this->actingAs(User::factory()->create())
        ->postJson('/api/v1/manage/venues', ['name' => 'Arena Baru', 'slug' => 'arena-baru', 'state' => 'johor'])
        ->assertForbidden()
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');

    expect(Venue::count())->toBe(0);
});

it('lets staff work without two-factor', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}")->assertOk();
});

it('lets a player see their (empty) venue list without two-factor', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/v1/manage/venues')->assertOk();
});

it('tells the web app whether two-factor is required and turned on', function () {
    $owner = User::factory()->create();
    Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->getJson('/api/v1/me')
        ->assertJsonPath('data.two_factor_required', true)
        ->assertJsonPath('data.two_factor_enabled', false);

    $this->actingAs(User::factory()->withTwoFactor()->create())->getJson('/api/v1/me')
        ->assertJsonPath('data.two_factor_required', false)
        ->assertJsonPath('data.two_factor_enabled', true);
});
