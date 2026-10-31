<?php

use App\Http\Middleware\TrustWebProxy;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| ADR-025: only the web app (which knows WEB_PROXY_SECRET) may say who the visitor is.
| Render's proxy reaches the API from a private address (10.x), like here.
| The visitor addresses are ordinary public ones on purpose: Symfony counts the
| "documentation" ranges (203.0.113.x, 198.51.100.x) as private, like 10.x.
*/

beforeEach(function () {
    config(['services.web_proxy.secret' => 'test-proxy-secret']);

    // Shows what the API believes about a request.
    Route::get('/test/who-is-this', fn (Request $request) => [
        'ip' => $request->ip(),
        'host' => $request->getHost(),
        'via' => $request->attributes->get('via'),
        'secret_visible' => $request->headers->has(TrustWebProxy::SECRET_HEADER),
    ]);
});

function fromRender(): array
{
    return ['REMOTE_ADDR' => '10.0.0.5'];
}

it('ignores IP addresses a direct caller writes into X-Forwarded-For', function () {
    // 6.6.6.6 is made up by the caller; Render added 34.120.0.9, the address it really saw.
    $this->withServerVariables(fromRender())
        ->withHeaders(['X-Forwarded-For' => '6.6.6.6, 34.120.0.9'])
        ->getJson('/test/who-is-this')
        ->assertOk()
        ->assertJson(['ip' => '34.120.0.9', 'via' => 'direct']);
});

it('ignores a host a direct caller makes up, so email links cannot point elsewhere', function () {
    $this->withServerVariables(fromRender())
        ->withHeaders(['X-Forwarded-Host' => 'evil.example', 'X-Forwarded-For' => '34.120.0.9'])
        ->getJson('/test/who-is-this')
        ->assertJson(['host' => 'localhost']);
});

it('ignores the visitor IP header without the right secret', function (?string $secret) {
    $headers = ['X-Forwarded-For' => '34.120.0.9', TrustWebProxy::CLIENT_IP_HEADER => '49.50.60.7'];
    if ($secret !== null) {
        $headers[TrustWebProxy::SECRET_HEADER] = $secret;
    }

    $this->withServerVariables(fromRender())
        ->withHeaders($headers)
        ->getJson('/test/who-is-this')
        ->assertJson(['ip' => '34.120.0.9', 'via' => 'direct']);
})->with(['no secret' => null, 'wrong secret' => 'guess', 'empty secret' => '']);

it('believes the web app about the visitor IP and host', function () {
    $this->withServerVariables(fromRender())
        ->withHeaders([
            TrustWebProxy::SECRET_HEADER => 'test-proxy-secret',
            TrustWebProxy::CLIENT_IP_HEADER => '49.50.60.7',
            // Whatever Vercel and Render put here doesn't matter any more.
            'X-Forwarded-For' => '6.6.6.6, 76.76.21.21',
            'X-Forwarded-Host' => 'courtku.vercel.app',
        ])
        ->getJson('/test/who-is-this')
        ->assertJson(['ip' => '49.50.60.7', 'host' => 'courtku.vercel.app', 'via' => 'browser']);
});

it('knows when the web app asks for itself, with no visitor behind it', function () {
    $this->withServerVariables(fromRender())
        ->withHeaders([TrustWebProxy::SECRET_HEADER => 'test-proxy-secret'])
        ->getJson('/test/who-is-this')
        ->assertJson(['via' => 'web-server']);
});

it('never passes the secret on to the rest of the app', function () {
    $this->withServerVariables(fromRender())
        ->withHeaders([TrustWebProxy::SECRET_HEADER => 'test-proxy-secret', TrustWebProxy::CLIENT_IP_HEADER => '49.50.60.7'])
        ->getJson('/test/who-is-this')
        ->assertJson(['secret_visible' => false]);
});

it('believes forwarded headers like before while no secret is set', function () {
    config(['services.web_proxy.secret' => null]);

    $this->withServerVariables(fromRender())
        ->withHeaders(['X-Forwarded-For' => '49.50.60.7, 76.76.21.21'])
        ->getJson('/test/who-is-this')
        ->assertJson(['ip' => '49.50.60.7', 'via' => 'direct']);
});

it('says in the health check which commit is running', function () {
    config(['app.version' => 'abc123']);

    $this->getJson('/api/health')->assertJsonPath('version', 'abc123');
});

it('says in the health check who the API thinks is asking', function () {
    $this->withHeaders([TrustWebProxy::SECRET_HEADER => 'test-proxy-secret', TrustWebProxy::CLIENT_IP_HEADER => '49.50.60.7'])
        ->getJson('/api/health')
        ->assertOk()
        ->assertJsonPath('via', 'browser');

    $this->flushHeaders()->getJson('/api/health')->assertJsonPath('via', 'direct');
});

it('gives each visitor 60 public reads a minute, and the web server a bucket of its own', function () {
    Venue::factory()->active()->create(['slug' => 'busy-arena']);
    $asVisitor = fn (string $ip) => $this->withServerVariables(fromRender())
        ->withHeaders([TrustWebProxy::SECRET_HEADER => 'test-proxy-secret', TrustWebProxy::CLIENT_IP_HEADER => $ip])
        ->getJson('/api/v1/venues/busy-arena');

    for ($i = 0; $i < 60; $i++) {
        $asVisitor('49.50.60.7')->assertOk();
    }
    $asVisitor('49.50.60.7')->assertStatus(429);

    // Other visitors, and the web app's server building the public page, are not blocked.
    $asVisitor('49.50.60.8')->assertOk();
    $this->withServerVariables(fromRender())
        ->withHeaders([TrustWebProxy::SECRET_HEADER => 'test-proxy-secret'])
        ->withoutHeader(TrustWebProxy::CLIENT_IP_HEADER)
        ->getJson('/api/v1/venues/busy-arena')
        ->assertOk();
});

it('adds the safety headers to every answer', function () {
    $this->getJson('/api/v1/does-not-exist')
        ->assertNotFound()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'")
        ->assertHeader('Referrer-Policy', 'no-referrer')
        // The Docker image's nginx sends X-Frame-Options; a second, different one would clash.
        ->assertHeaderMissing('X-Frame-Options');
});

it('tells browsers to stay on HTTPS only when the request came over HTTPS', function () {
    $this->getJson('/api/health')->assertHeaderMissing('Strict-Transport-Security');

    $this->withServerVariables(fromRender())
        ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '34.120.0.9'])
        ->getJson('/api/health')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
});
