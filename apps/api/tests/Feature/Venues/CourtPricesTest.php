<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;

/** An owner (with two-factor) and their venue. */
function pricesOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

it('FR-09 saves a court\'s price per hour in sen, and shows it in ringgit too', function () {
    [$owner, $venue] = pricesOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Court 1', 'is_indoor' => true, 'base_price_sen' => 2550, 'sports' => ['badminton'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.base_price_sen', 2550)
        ->assertJsonPath('data.base_price', 'RM 25.50');
});

it('ADR-017 only accepts prices in 10-sen steps, up to RM 1,000 an hour', function (int $sen) {
    [$owner, $venue] = pricesOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Court 1', 'is_indoor' => true, 'base_price_sen' => $sen, 'sports' => ['badminton'],
    ])->assertStatus(422)->assertJsonValidationErrors('base_price_sen', 'error.details.fields');
})->with([
    'RM 25.25 can\'t be split into two half hours' => [2525],
    'over RM 1,000' => [100010],
    'negative' => [-100],
]);

it('keeps the price when it is left out, and clears it when sent as empty', function () {
    [$owner, $venue] = pricesOwnerAndVenue();
    $court = Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 3000]);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}", [
        'name' => 'Court A', 'is_indoor' => true, 'sports' => ['badminton'],
    ])->assertOk()->assertJsonPath('data.base_price_sen', 3000);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}", [
        'name' => 'Court A', 'is_indoor' => true, 'base_price_sen' => null, 'sports' => ['badminton'],
    ])->assertOk()->assertJsonPath('data.base_price', null);
});

it('hides courts without a price from players until the owner sets one', function () {
    $venue = Venue::factory()->active()->create(['slug' => 'arena']);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Priced']);
    $unpriced = Court::factory()->for($venue)->offering('badminton')->unpriced()->create(['name' => 'Not priced yet']);

    $this->getJson('/api/v1/venues/arena')
        ->assertJsonCount(1, 'data.courts')
        ->assertJsonPath('data.courts.0.name', 'Priced')
        ->assertJsonPath('data.courts.0.base_price', 'RM 25.00');
    $this->getJson('/api/v1/venues/arena/courts')->assertJsonCount(1, 'data');

    $unpriced->update(['base_price_sen' => 2000]);
    $this->getJson('/api/v1/venues/arena')->assertJsonCount(2, 'data.courts');
});
