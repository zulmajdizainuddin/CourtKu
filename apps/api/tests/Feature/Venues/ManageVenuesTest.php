<?php

use App\Models\User;
use App\Models\Venue;

it('lists only the venues the user works at, with their role at each', function () {
    $user = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($user)->create(['name' => 'B Arena']);
    Venue::factory()->active()->withStaff($user)->create(['name' => 'A Court']);
    Venue::factory()->active()->create(['name' => 'Someone Else']);

    $this->actingAs($user)->getJson('/api/v1/manage/venues')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'A Court')
        ->assertJsonPath('data.0.role', 'staff')
        ->assertJsonPath('data.1.name', 'B Arena')
        ->assertJsonPath('data.1.role', 'owner');
});

it('shows a venue to its owner and its staff', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $staff = User::factory()->create();
    $venue = Venue::factory()->ownedBy($owner)->withStaff($staff)->create();

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")
        ->assertOk()
        ->assertJsonPath('data.role', 'owner');

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}")
        ->assertOk()
        ->assertJsonPath('data.role', 'staff');
});
