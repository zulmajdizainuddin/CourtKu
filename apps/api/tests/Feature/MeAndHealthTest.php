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

it('NFR-03 reports down quickly when the database is unreachable', function () {
    // A connection to a port where nothing listens, standing in for a powered-off database.
    config(['database.connections.unreachable' => array_merge(config('database.connections.mysql'), [
        'host' => '127.0.0.1',
        'port' => 1,
    ])]);
    $original = config('database.default');
    config(['database.default' => 'unreachable', 'cache.default' => 'database']);

    try {
        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJson(['status' => 'down', 'checks' => ['database' => false, 'cache' => false]]);
    } finally {
        config(['database.default' => $original]);
    }
});

it('answers /up without touching the database', function () {
    config(['database.connections.unreachable' => array_merge(config('database.connections.mysql'), [
        'host' => '127.0.0.1',
        'port' => 1,
    ])]);
    $original = config('database.default');
    config(['database.default' => 'unreachable']);

    try {
        $this->get('/up')->assertOk();
    } finally {
        config(['database.default' => $original]);
    }
});
