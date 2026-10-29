<?php

use App\Models\Court;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;

/** An owner (with two-factor) and their venue. */
function courtsOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

it('has badminton, futsal and pickleball from the migration, so the live site has them too', function () {
    expect(Sport::orderBy('id')->pluck('slug')->all())->toBe(['badminton', 'futsal', 'pickleball']);
});

it('FR-08 adds a court at the end of the list', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'sort_order' => 0]);

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Court 2', 'is_indoor' => false, 'sports' => ['pickleball'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Court 2')
        ->assertJsonPath('data.is_indoor', false)
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.sort_order', 1)
        ->assertJsonPath('data.sports', ['pickleball']);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/courts")
        ->assertJsonPath('data.0.name', 'Court 1')
        ->assertJsonPath('data.1.name', 'Court 2');
});

it('ADR-014 lets one court offer several sports', function () {
    [$owner, $venue] = courtsOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Hall A', 'is_indoor' => true, 'sports' => ['futsal', 'badminton'],
    ])->assertCreated()->assertJsonPath('data.sports', ['badminton', 'futsal']);
});

it('checks the name and the sports', function (array $input, string $field) {
    [$owner, $venue] = courtsOwnerAndVenue();
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1']);

    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/courts", array_merge(
            ['name' => 'Court 2', 'is_indoor' => true, 'sports' => ['badminton']],
            $input,
        ))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    'no name' => [['name' => ''], 'name'],
    'name already used here' => [['name' => 'Court 1'], 'name'],
    'no sport' => [['sports' => []], 'sports'],
    'unknown sport' => [['sports' => ['tennis']], 'sports.0'],
    'indoor missing' => [['is_indoor' => null], 'is_indoor'],
]);

it('allows the same court name at another venue', function () {
    Court::factory()->for(Venue::factory()->create())->create(['name' => 'Court 1']);
    [$owner, $venue] = courtsOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Court 1', 'is_indoor' => true, 'sports' => ['badminton'],
    ])->assertCreated();
});

it('changes a court: name, sports, and deactivates it', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    $court = Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}", [
        'name' => 'Court A', 'is_indoor' => true, 'is_active' => false, 'sports' => ['futsal'],
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Court A')
        ->assertJsonPath('data.is_active', false)
        ->assertJsonPath('data.sports', ['futsal']);

    // Saving with its own name again is fine.
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}", [
        'name' => 'Court A', 'is_indoor' => true, 'sports' => ['futsal'],
    ])->assertOk();
});

it('removes a court and its sports', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    $court = Court::factory()->for($venue)->offering('badminton', 'futsal')->create();

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}")->assertNoContent();

    expect(Court::count())->toBe(0)
        ->and(DB::table('court_sports')->count())->toBe(0);
});

it('puts the courts in the owner\'s order', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    $first = Court::factory()->for($venue)->create(['name' => 'Court 1', 'sort_order' => 0]);
    $second = Court::factory()->for($venue)->create(['name' => 'Court 2', 'sort_order' => 1]);
    $third = Court::factory()->for($venue)->create(['name' => 'Court 3', 'sort_order' => 2]);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/order", [
        'court_ids' => [$third->id, $first->id, $second->id],
    ])
        ->assertOk()
        ->assertJsonPath('data.*.name', ['Court 3', 'Court 1', 'Court 2']);
});

it('refuses an order that leaves out a court or includes another venue\'s court', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    $court = Court::factory()->for($venue)->create();
    Court::factory()->for($venue)->create();
    $otherVenuesCourt = Court::factory()->for(Venue::factory()->create())->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/courts/order", [
        'court_ids' => [$court->id, $otherVenuesCourt->id],
    ])->assertStatus(422)->assertJsonValidationErrors('court_ids', 'error.details.fields');
});

it('stops at 50 courts per venue', function () {
    [$owner, $venue] = courtsOwnerAndVenue();
    Court::factory()->count(50)->for($venue)->create();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'One too many', 'is_indoor' => true, 'sports' => ['badminton'],
    ])->assertStatus(409)->assertJsonPath('error.code', 'COURT_LIMIT');
});

it('lets staff see the courts but not change them', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();
    $court = Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1']);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/courts")->assertOk()->assertJsonCount(1, 'data');

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/courts", [
        'name' => 'Court 2', 'is_indoor' => true, 'sports' => ['badminton'],
    ])->assertForbidden();
    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}", [
        'name' => 'Renamed', 'is_indoor' => true, 'sports' => ['badminton'],
    ])->assertForbidden();
    $this->actingAs($staff)->deleteJson("/api/v1/manage/venues/{$venue->id}/courts/{$court->id}")->assertForbidden();

    expect($court->fresh()->name)->toBe('Court 1');
});
