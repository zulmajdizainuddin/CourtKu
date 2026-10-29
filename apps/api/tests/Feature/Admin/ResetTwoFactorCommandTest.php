<?php

use App\Models\User;
use App\Models\Venue;

it('turns two-factor off for a locked-out owner, after the identity check', function () {
    $owner = User::factory()->withTwoFactor()->create(['name' => 'Ali', 'email' => 'ali@example.com']);
    Venue::factory()->ownedBy($owner)->create();

    $this->artisan('courtku:reset-two-factor', ['email' => 'Ali@Example.com'])
        ->expectsConfirmation('Did you check that this request really comes from Ali (ali@example.com)?', 'yes')
        ->assertSuccessful();

    $owner->refresh();
    expect($owner->two_factor_secret)->toBeNull()
        ->and($owner->two_factor_recovery_codes)->toBeNull()
        ->and($owner->hasEnabledTwoFactorAuthentication())->toBeFalse();

    // Still an owner, so the dashboard asks them to set it up again.
    $this->actingAs($owner)->getJson('/api/v1/manage/venues')
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');
});

it('changes nothing when the identity was not checked', function () {
    $user = User::factory()->withTwoFactor()->create(['name' => 'Ali', 'email' => 'ali@example.com']);

    $this->artisan('courtku:reset-two-factor', ['email' => 'ali@example.com'])
        ->expectsConfirmation('Did you check that this request really comes from Ali (ali@example.com)?', 'no')
        ->assertFailed();

    expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

it('fails for an email nobody has registered', function () {
    $this->artisan('courtku:reset-two-factor', ['email' => 'nobody@example.com'])->assertFailed();
});

it('says so when two-factor is already off', function () {
    User::factory()->create(['email' => 'ali@example.com']);

    $this->artisan('courtku:reset-two-factor', ['email' => 'ali@example.com'])
        ->expectsOutputToContain('already off')
        ->assertSuccessful();
});
