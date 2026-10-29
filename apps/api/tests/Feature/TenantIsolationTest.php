<?php

use App\Models\User;
use App\Models\Venue;

/*
| ADR-002: one database serves every venue, so a missing check would show venue B's
| data to venue A. Every manage endpoint with a {venue} is listed here, and someone who
| works at venue A (owner or staff) must get 404 for venue B.
|
| Adding a manage endpoint? Add it to this list.
*/

dataset('manage endpoints', [
    'view venue' => ['GET', '/api/v1/manage/venues/{venue}'],
]);

it('ADR-002 hides venue B from the owner of venue A', function (string $method, string $path) {
    $ownerOfA = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($ownerOfA)->create();
    $venueB = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs($ownerOfA)
        ->json($method, str_replace('{venue}', (string) $venueB->id, $path))
        ->assertNotFound();
})->with('manage endpoints');

it('ADR-002 hides venue B from the staff of venue A', function (string $method, string $path) {
    $staffOfA = User::factory()->create();
    Venue::factory()->active()->withStaff($staffOfA)->create();
    $venueB = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs($staffOfA)
        ->json($method, str_replace('{venue}', (string) $venueB->id, $path))
        ->assertNotFound();
})->with('manage endpoints');

it('ADR-002 hides every venue from a player who works nowhere', function (string $method, string $path) {
    $venue = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs(User::factory()->create())
        ->json($method, str_replace('{venue}', (string) $venue->id, $path))
        ->assertNotFound();
})->with('manage endpoints');
