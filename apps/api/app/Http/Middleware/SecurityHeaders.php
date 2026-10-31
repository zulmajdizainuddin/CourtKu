<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safety headers on every API answer (docs/security.md). The API only sends JSON and a few
 * redirects, so it can be strict: no framing, no guessing file types, no scripts or styles.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Browsers must not guess that a JSON answer is HTML or a script.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Nothing an API answer could load is allowed, so even injected HTML can't run anything,
        // and no other site may show an API answer inside a frame (frame-ancestors). No
        // X-Frame-Options here: the Docker image's nginx already sends one, and two different
        // values would confuse browsers. frame-ancestors wins over it in every current browser.
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
        // Links followed from an API answer don't tell the next site the full address.
        $response->headers->set('Referrer-Policy', 'no-referrer');

        // Once a browser has seen the API over HTTPS, it refuses plain HTTP for a year.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
