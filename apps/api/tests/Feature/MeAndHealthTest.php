<?php

use App\Models\User;

it('returns the logged-in user from /api/v1/me', function () {
    $user = User::factory()->create(['name' => 'Aina Rahman']);

    $this->actingAs($user)->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.name', 'Aina Rahman')
        ->assertJsonMissingPath('data.password');
});

it('returns 401 with the standard error shape when logged out', function () {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('returns 404 with the standard error shape for unknown API routes', function () {
    $this->getJson('/api/v1/does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('NFR-03 reports healthy when the database and cache respond', function () {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertJson(['status' => 'ok', 'checks' => ['database' => true, 'cache' => true]]);
});
