<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

/** The 6-digit code the user's authenticator app would show right now. */
function currentCode(User $user): string
{
    return app(Google2FA::class)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));
}

it('asks for the password again before turning two-factor on', function () {
    $this->actingAs(User::factory()->create())
        ->postJson('/auth/user/two-factor-authentication')
        ->assertStatus(423)
        ->assertJsonPath('error.code', 'PASSWORD_CONFIRMATION_REQUIRED');
});

it('turns two-factor on: QR code, then a code from the app confirms it', function () {
    $user = User::factory()->create(['password' => 'Secret-Pass-123']);
    $this->actingAs($user);

    $this->postJson('/auth/user/confirm-password', ['password' => 'Secret-Pass-123'])->assertCreated();
    $this->postJson('/auth/user/two-factor-authentication')->assertOk();

    $this->getJson('/auth/user/two-factor-qr-code')
        ->assertOk()
        ->assertJsonStructure(['svg', 'url']);

    // Not on yet: scanning the QR code is not enough until a code proves it worked.
    $this->getJson('/api/v1/me')->assertJsonPath('data.two_factor_enabled', false);

    $this->postJson('/auth/user/confirmed-two-factor-authentication', ['code' => currentCode($user)])->assertOk();

    $this->getJson('/api/v1/me')->assertJsonPath('data.two_factor_enabled', true);
    $this->getJson('/auth/user/two-factor-recovery-codes')->assertOk()->assertJsonCount(8);
});

it('refuses to turn two-factor on with a wrong code', function () {
    $user = User::factory()->create(['password' => 'Secret-Pass-123']);
    $this->actingAs($user);

    $this->postJson('/auth/user/confirm-password', ['password' => 'Secret-Pass-123']);
    $this->postJson('/auth/user/two-factor-authentication');

    $this->postJson('/auth/user/confirmed-two-factor-authentication', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('asks for a code after the password, and only logs in once it is right', function () {
    $user = User::factory()->withTwoFactor()->create(['password' => 'Secret-Pass-123']);

    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'Secret-Pass-123'])
        ->assertOk()
        ->assertJsonPath('two_factor', true);
    $this->assertGuest();

    $this->postJson('/auth/two-factor-challenge', ['code' => '000000'])->assertStatus(422);
    $this->assertGuest();

    $this->postJson('/auth/two-factor-challenge', ['code' => currentCode($user)])->assertNoContent();
    $this->assertAuthenticatedAs($user);
});

it('logs in with a recovery code, which then cannot be used again', function () {
    $user = User::factory()->withTwoFactor()->create(['password' => 'Secret-Pass-123']);
    $recoveryCode = $user->recoveryCodes()[0];

    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'Secret-Pass-123']);
    $this->postJson('/auth/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertNoContent();
    $this->assertAuthenticatedAs($user);

    expect($user->fresh()->recoveryCodes())->not->toContain($recoveryCode);
});

it('blocks guessing codes after 5 tries in a minute', function () {
    $user = User::factory()->withTwoFactor()->create(['password' => 'Secret-Pass-123']);
    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'Secret-Pass-123']);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/auth/two-factor-challenge', ['code' => '000000']);
    }

    $this->postJson('/auth/two-factor-challenge', ['code' => currentCode($user)])
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
    $this->assertGuest();
});
