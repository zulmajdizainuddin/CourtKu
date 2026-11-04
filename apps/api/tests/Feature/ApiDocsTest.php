<?php

/* ADR-035: the API documentation, served by CourtKu itself at /docs. */

it('shows the API docs page, with a security rule that lets its own scripts and styles load', function () {
    $response = $this->get('/docs')->assertOk()->assertSee('CourtKu API');

    $policy = (string) $response->headers->get('Content-Security-Policy');
    expect($policy)->toContain("script-src 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("form-action 'none'");
});

it('offers the Postman collection and the OpenAPI description', function () {
    $this->get('/docs.postman')->assertOk()->assertHeader('Content-Type', 'application/json');
    $this->get('/docs.openapi')->assertOk()->assertHeader('Content-Type', 'application/yaml');
});

it('keeps the strict rule on every other API answer', function () {
    $this->getJson('/api/health')->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
});

it('documents the main endpoints, but not the internal ones', function () {
    $openapi = (string) file_get_contents(resource_path('docs/openapi.yaml'));

    expect($openapi)->toContain('/api/v1/venues/{venue_slug}/bookings')
        ->toContain('/auth/login')
        ->not->toContain('/api/tick')
        ->not->toContain('pretend-checkout')
        ->not->toContain('test-error');
});
