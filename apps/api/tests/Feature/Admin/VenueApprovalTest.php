<?php

use App\Enums\VenueStatus;
use App\Models\User;
use App\Models\Venue;

function admin(): User
{
    return User::factory()->create()->forceFill(['is_platform_admin' => true]);
}

it('FR-33 lists venues for the admin with their owners, newest first', function () {
    $owner = User::factory()->create(['name' => 'Farid Owner']);
    Venue::factory()->active()->create(['name' => 'Older']);
    Venue::factory()->ownedBy($owner)->create(['name' => 'Newer']);

    $this->actingAs(admin())->getJson('/api/v1/admin/venues')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Newer')
        ->assertJsonPath('data.0.owners.0.name', 'Farid Owner')
        ->assertJsonPath('data.1.name', 'Older');
});

it('FR-33 filters the list by status', function () {
    Venue::factory()->create(['name' => 'Waiting']);
    Venue::factory()->active()->create();
    Venue::factory()->suspended()->create();

    $this->actingAs(admin())->getJson('/api/v1/admin/venues?status=pending')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Waiting');

    $this->actingAs(admin())->getJson('/api/v1/admin/venues?status=closed')->assertStatus(422);
});

it('FR-04 approves a pending venue', function () {
    $venue = Venue::factory()->create();

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$venue->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'active');

    $venue->refresh();
    expect($venue->status)->toBe(VenueStatus::Active)
        ->and($venue->approved_at)->not->toBeNull();
});

it('FR-33 suspends an active venue, and can reinstate it', function () {
    $venue = Venue::factory()->active()->create();

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$venue->id}/suspend")
        ->assertOk()
        ->assertJsonPath('data.status', 'suspended');

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$venue->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.suspended_at', null);
});

it('turns down a pending venue by suspending it', function () {
    $venue = Venue::factory()->create();

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$venue->id}/suspend")
        ->assertOk()
        ->assertJsonPath('data.status', 'suspended');
});

it('refuses a status change that changes nothing', function () {
    $active = Venue::factory()->active()->create();
    $suspended = Venue::factory()->suspended()->create();

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$active->id}/approve")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INVALID_STATUS_CHANGE');

    $this->actingAs(admin())->postJson("/api/v1/admin/venues/{$suspended->id}/suspend")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INVALID_STATUS_CHANGE');
});

it('keeps every admin endpoint away from everyone else, owners included', function (string $method, string $path) {
    $owner = User::factory()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)
        ->json($method, str_replace('{venue}', (string) $venue->id, $path))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');

    expect($venue->refresh()->status)->toBe(VenueStatus::Pending);
})->with([
    'list' => ['GET', '/api/v1/admin/venues'],
    'approve' => ['POST', '/api/v1/admin/venues/{venue}/approve'],
    'suspend' => ['POST', '/api/v1/admin/venues/{venue}/suspend'],
]);
