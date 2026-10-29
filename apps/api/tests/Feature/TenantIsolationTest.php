<?php

use App\Models\Court;
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
    'update profile' => ['PUT', '/api/v1/manage/venues/{venue}'],
    'view hours' => ['GET', '/api/v1/manage/venues/{venue}/hours'],
    'update hours' => ['PUT', '/api/v1/manage/venues/{venue}/hours'],
    'list closures' => ['GET', '/api/v1/manage/venues/{venue}/closures'],
    'add closure' => ['POST', '/api/v1/manage/venues/{venue}/closures'],
    'remove closure' => ['DELETE', '/api/v1/manage/venues/{venue}/closures/{closure}'],
    'list courts' => ['GET', '/api/v1/manage/venues/{venue}/courts'],
    'add court' => ['POST', '/api/v1/manage/venues/{venue}/courts'],
    'reorder courts' => ['PUT', '/api/v1/manage/venues/{venue}/courts/order'],
    'change court' => ['PUT', '/api/v1/manage/venues/{venue}/courts/{court}'],
    'remove court' => ['DELETE', '/api/v1/manage/venues/{venue}/courts/{court}'],
]);

/** Fills in {venue}, and {closure} or {court} with one of that venue's own. */
function urlFor(string $path, Venue $venue): string
{
    if (str_contains($path, '{court}')) {
        $path = str_replace('{court}', (string) Court::factory()->for($venue)->create()->id, $path);
    }
    if (str_contains($path, '{closure}')) {
        $closure = $venue->closures()->create(['starts_on' => '2099-01-01', 'ends_on' => '2099-01-02', 'reason' => 'Test']);
        $path = str_replace('{closure}', (string) $closure->id, $path);
    }

    return str_replace('{venue}', (string) $venue->id, $path);
}

it('ADR-002 hides venue B from the owner of venue A', function (string $method, string $path) {
    $ownerOfA = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($ownerOfA)->create();
    $venueB = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs($ownerOfA)->json($method, urlFor($path, $venueB))->assertNotFound();
})->with('manage endpoints');

it('ADR-002 hides venue B from the staff of venue A', function (string $method, string $path) {
    $staffOfA = User::factory()->create();
    Venue::factory()->active()->withStaff($staffOfA)->create();
    $venueB = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs($staffOfA)->json($method, urlFor($path, $venueB))->assertNotFound();
})->with('manage endpoints');

it('ADR-002 hides every venue from a player who works nowhere', function (string $method, string $path) {
    $venue = Venue::factory()->active()->ownedBy(User::factory()->create())->create();

    $this->actingAs(User::factory()->create())->json($method, urlFor($path, $venue))->assertNotFound();
})->with('manage endpoints');

it('ADR-002 never reaches venue B\'s closure through venue A\'s address', function () {
    $ownerOfA = User::factory()->withTwoFactor()->create();
    $venueA = Venue::factory()->active()->ownedBy($ownerOfA)->create();
    $venueB = Venue::factory()->active()->ownedBy(User::factory()->create())->create();
    $closureOfB = $venueB->closures()->create(['starts_on' => '2099-01-01', 'ends_on' => '2099-01-02', 'reason' => 'Renovation']);

    $this->actingAs($ownerOfA)
        ->deleteJson("/api/v1/manage/venues/{$venueA->id}/closures/{$closureOfB->id}")
        ->assertNotFound();

    expect($closureOfB->fresh())->not->toBeNull();
});

it('ADR-002 never reaches venue B\'s court through venue A\'s address', function () {
    $ownerOfA = User::factory()->withTwoFactor()->create();
    $venueA = Venue::factory()->active()->ownedBy($ownerOfA)->create();
    $courtOfB = Court::factory()->for(Venue::factory()->create())->offering('badminton')->create(['name' => 'Court B']);

    $this->actingAs($ownerOfA)
        ->putJson("/api/v1/manage/venues/{$venueA->id}/courts/{$courtOfB->id}", [
            'name' => 'Taken over', 'is_indoor' => true, 'sports' => ['badminton'],
        ])
        ->assertNotFound();
    $this->actingAs($ownerOfA)->deleteJson("/api/v1/manage/venues/{$venueA->id}/courts/{$courtOfB->id}")->assertNotFound();

    expect($courtOfB->fresh()->name)->toBe('Court B');
});
