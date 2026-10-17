<?php

use App\Models\User;

it('FR-01 logs in with the right email and password', function () {
    $user = User::factory()->create(['password' => 'Secret-Pass-123']);

    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'Secret-Pass-123'])
        ->assertOk();

    $this->assertAuthenticatedAs($user);
});

it('FR-01 refuses a wrong password', function () {
    $user = User::factory()->create(['password' => 'Secret-Pass-123']);

    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');

    $this->assertGuest();
});

it('blocks login after 5 attempts in a minute', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->postJson('/auth/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
});

it('FR-01 logs out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/auth/logout')->assertNoContent();

    $this->assertGuest('web');
});
