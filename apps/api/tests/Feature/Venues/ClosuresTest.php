<?php

use App\Models\User;
use App\Models\Venue;

/** An owner (with two-factor) and their venue. */
function closuresOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

beforeEach(function () {
    // 1 Nov 2026, 10:00 in Malaysia.
    $this->travelTo('2026-11-01 02:00:00');
});

it('FR-07 adds a closed period, e.g. Hari Raya', function () {
    [$owner, $venue] = closuresOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2027-03-30', 'ends_on' => '2027-04-01', 'reason' => 'Hari Raya Aidilfitri',
    ])
        ->assertCreated()
        ->assertJsonPath('data.starts_on', '2027-03-30')
        ->assertJsonPath('data.ends_on', '2027-04-01');

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/closures")
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.reason', 'Hari Raya Aidilfitri');
});

it('lists only today\'s and future closures, soonest first', function () {
    [$owner, $venue] = closuresOwnerAndVenue();
    $venue->closures()->create(['starts_on' => '2026-12-24', 'ends_on' => '2026-12-26', 'reason' => 'Christmas']);
    $venue->closures()->create(['starts_on' => '2026-10-28', 'ends_on' => '2026-11-01', 'reason' => 'Ends today']);
    $venue->closures()->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-02', 'reason' => 'Already over']);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/closures")
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.reason', 'Ends today')
        ->assertJsonPath('data.1.reason', 'Christmas');
});

it('uses today in Malaysia, not in UTC', function () {
    [$owner, $venue] = closuresOwnerAndVenue();
    // 31 Oct 2026, 17:00 UTC is already 1 Nov, 01:00 in Malaysia.
    $this->travelTo('2026-10-31 17:00:00');

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2026-10-31', 'ends_on' => '2026-10-31', 'reason' => 'Yesterday in Malaysia',
    ])->assertStatus(422)->assertJsonValidationErrors('starts_on', 'error.details.fields');

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2026-11-01', 'ends_on' => '2026-11-01', 'reason' => 'Today in Malaysia',
    ])->assertCreated();
});

it('checks the dates and the reason', function (array $input, string $field) {
    [$owner, $venue] = closuresOwnerAndVenue();
    $venue->closures()->create(['starts_on' => '2026-12-24', 'ends_on' => '2026-12-26', 'reason' => 'Christmas']);

    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/closures", array_merge(
            ['starts_on' => '2027-01-10', 'ends_on' => '2027-01-11', 'reason' => 'Renovation'],
            $input,
        ))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    'starts in the past' => [['starts_on' => '2026-10-20'], 'starts_on'],
    'ends before it starts' => [['ends_on' => '2027-01-09'], 'ends_on'],
    'longer than a year' => [['ends_on' => '2028-01-20'], 'ends_on'],
    'overlaps another closure' => [['starts_on' => '2026-12-26', 'ends_on' => '2026-12-28'], 'starts_on'],
    'no reason' => [['reason' => ''], 'reason'],
    'not a date' => [['starts_on' => '30/03/2027'], 'starts_on'],
]);

it('removes a closure', function () {
    [$owner, $venue] = closuresOwnerAndVenue();
    $closure = $venue->closures()->create(['starts_on' => '2026-12-24', 'ends_on' => '2026-12-26', 'reason' => 'Christmas']);

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/closures/{$closure->id}")->assertNoContent();

    expect($venue->closures()->count())->toBe(0);
});

it('lets staff read closures but not add or remove them', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();
    $closure = $venue->closures()->create(['starts_on' => '2026-12-24', 'ends_on' => '2026-12-26', 'reason' => 'Christmas']);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/closures")->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/closures", [
        'starts_on' => '2027-01-10', 'ends_on' => '2027-01-11', 'reason' => 'Renovation',
    ])->assertForbidden();
    $this->actingAs($staff)->deleteJson("/api/v1/manage/venues/{$venue->id}/closures/{$closure->id}")->assertForbidden();

    expect($venue->closures()->count())->toBe(1);
});
