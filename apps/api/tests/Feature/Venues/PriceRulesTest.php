<?php

use App\Models\Court;
use App\Models\PriceRule;
use App\Models\User;
use App\Models\Venue;

/** An owner (with two-factor) and their venue. */
function priceRulesOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

/** "Mon–Fri, 6:00 pm to midnight, RM 30.00 an hour, all courts", with any field changed. */
function peakInput(array $overrides = []): array
{
    return [
        'name' => 'Weekday peak',
        'court_id' => null,
        'weekdays' => [5, 1, 2, 3, 4],
        'starts_at' => '18:00',
        'ends_at' => '00:00',
        'price_sen' => 3000,
        ...$overrides,
    ];
}

it('FR-10 adds a peak price for all courts', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput())
        ->assertCreated()
        ->assertJsonPath('data.court_id', null)
        ->assertJsonPath('data.weekdays', [1, 2, 3, 4, 5]) // sorted
        ->assertJsonPath('data.starts_at', '18:00')
        ->assertJsonPath('data.ends_at', '00:00')
        ->assertJsonPath('data.ends_next_day', true) // midnight is the end of the day
        ->assertJsonPath('data.price', 'RM 30.00');

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/price-rules")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Weekday peak');
});

it('adds a rule for one court, even on the same hours as an all-courts rule', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();
    $court = Court::factory()->for($venue)->create();
    PriceRule::factory()->for($venue)->create();

    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput(['court_id' => $court->id, 'price_sen' => 4500]))
        ->assertCreated()
        ->assertJsonPath('data.court_id', $court->id);

    // All-courts rules come first.
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/price-rules")
        ->assertJsonPath('data.0.court_id', null)
        ->assertJsonPath('data.1.court_id', $court->id);
});

it('ADR-020 refuses a rule that overlaps another for the same courts, and says which', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();
    PriceRule::factory()->for($venue)->create();

    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput([
            'name' => 'Friday late', 'weekdays' => [5], 'starts_at' => '22:00', 'ends_at' => '02:00',
        ]))
        ->assertStatus(422)
        ->assertJsonPath(
            'error.details.fields.starts_at.0',
            'This overlaps Weekday peak (Mon–Fri, 6:00 pm – midnight) for all courts. Change the days or times so each half hour has one price.',
        );

    // Starting when the peak ends is fine.
    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput([
            'name' => 'Friday late', 'weekdays' => [5], 'starts_at' => '00:00', 'ends_at' => '18:00',
        ]))
        ->assertCreated();
});

it('changes a rule without it clashing with itself, and removes it', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();
    $rule = PriceRule::factory()->for($venue)->create();

    $this->actingAs($owner)
        ->putJson("/api/v1/manage/venues/{$venue->id}/price-rules/{$rule->id}", peakInput(['ends_at' => '02:00', 'price_sen' => 3550]))
        ->assertOk()
        ->assertJsonPath('data.ends_at', '02:00')
        ->assertJsonPath('data.ends_next_day', true)
        ->assertJsonPath('data.price', 'RM 35.50');

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/price-rules/{$rule->id}")->assertNoContent();

    expect($venue->priceRules()->count())->toBe(0);
});

it('refuses a court of another venue', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();
    $otherCourt = Court::factory()->for(Venue::factory()->create())->create();

    $this->actingAs($owner)
        ->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput(['court_id' => $otherCourt->id]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('court_id', 'error.details.fields');
});

it('checks the days, times and price', function (array $input, string $field) {
    [$owner, $venue] = priceRulesOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput($input))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    'no days' => [['weekdays' => []], 'weekdays'],
    'day 7' => [['weekdays' => [7]], 'weekdays.0'],
    'the same day twice' => [['weekdays' => [1, 1]], 'weekdays.0'],
    'not on the half hour' => [['starts_at' => '18:15'], 'starts_at'],
    'start and end the same' => [['ends_at' => '18:00'], 'ends_at'],
    'no price' => [['price_sen' => null], 'price_sen'],
    'not in 10-sen steps' => [['price_sen' => 3005], 'price_sen'],
    'over RM 1,000' => [['price_sen' => 100010], 'price_sen'],
    'a long name' => [['name' => str_repeat('a', 51)], 'name'],
]);

it('stops at 50 rules', function () {
    [$owner, $venue] = priceRulesOwnerAndVenue();
    PriceRule::factory()->for($venue)->count(50)->create(['weekdays' => [0]]);

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput())
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'PRICE_RULE_LIMIT');
});

it('lets staff see the prices but not change them', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();
    $rule = PriceRule::factory()->for($venue)->create();

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/price-rules")->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/price-rules", peakInput(['weekdays' => [0]]))->assertForbidden();
    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/price-rules/{$rule->id}", peakInput())->assertForbidden();
    $this->actingAs($staff)->deleteJson("/api/v1/manage/venues/{$venue->id}/price-rules/{$rule->id}")->assertForbidden();
});
