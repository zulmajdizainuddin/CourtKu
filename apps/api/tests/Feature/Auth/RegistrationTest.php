<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

$validPlayer = fn (array $overrides = []) => array_merge([
    'name' => 'Aina Rahman',
    'email' => 'aina@example.com',
    'password' => 'Secret-Pass-123',
    'password_confirmation' => 'Secret-Pass-123',
], $overrides);

it('FR-01 registers a player and logs them in', function () use ($validPlayer) {
    $this->postJson('/auth/register', $validPlayer())->assertCreated();

    $this->assertAuthenticated();
    expect(User::where('email', 'aina@example.com')->exists())->toBeTrue();
});

it('FR-02 sends a verification email after registering', function () use ($validPlayer) {
    Notification::fake();

    $this->postJson('/auth/register', $validPlayer());

    Notification::assertSentTo(User::firstWhere('email', 'aina@example.com'), VerifyEmail::class);
});

it('FR-01 refuses an email that is already registered', function () use ($validPlayer) {
    User::factory()->create(['email' => 'aina@example.com']);

    $this->postJson('/auth/register', $validPlayer())
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED')
        ->assertJsonStructure(['error' => ['details' => ['fields' => ['email']]]]);
});

it('FR-01 refuses a password that does not match its confirmation', function () use ($validPlayer) {
    $this->postJson('/auth/register', $validPlayer(['password_confirmation' => 'something-else']))
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['fields' => ['password']]]]);
});

it('does not let anyone register themselves as platform admin', function () use ($validPlayer) {
    $this->postJson('/auth/register', $validPlayer(['is_platform_admin' => true]));

    expect(User::firstWhere('email', 'aina@example.com')->is_platform_admin)->toBeFalse();
});
