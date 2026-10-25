<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // "Is the app running?" for the host's health check. Never touches the database,
        // so a database outage doesn't make the host restart a working API.
        // The full check (database + cache) is /api/health, used by the uptime monitor.
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Lets the Next.js app log in with Sanctum's session cookie instead of tokens.
        $middleware->statefulApi();

        // Every browser request reaches us through the Next.js proxy (Vercel in production).
        // Trusting it means request()->ip() and generated links use the visitor's real
        // IP and the web app's host, not the proxy's. See ADR-004.
        // TODO(M5): before going live, only accept requests that come from the proxy,
        // otherwise someone calling the API directly could fake their IP.
        $middleware->trustProxies(at: '*');

        // Logged-out visitors are sent to the web app's login page.
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isJson = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($isJson);

        // Every API error has the same shape:
        // { "error": { "code": "SLOT_TAKEN", "message": "...", "details": {...} } }
        $error = fn (string $code, string $message, int $status, array $details = []) => response()->json([
            'error' => ['code' => $code, 'message' => $message, 'details' => (object) $details],
        ], $status);

        $exceptions->render(function (ValidationException $e, Request $request) use ($isJson, $error) {
            if ($isJson($request)) {
                return $error('VALIDATION_FAILED', $e->getMessage(), 422, ['fields' => $e->errors()]);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isJson, $error) {
            if ($isJson($request)) {
                return $error('UNAUTHENTICATED', 'Please log in first.', 401);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($isJson, $error) {
            if ($isJson($request)) {
                return $error('TOO_MANY_REQUESTS', 'Too many attempts. Please wait and try again.', 429)
                    ->withHeaders($e->getHeaders());
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isJson, $error) {
            if ($isJson($request)) {
                return $error('NOT_FOUND', 'Not found.', 404);
            }
        });

        // Any other HTTP error, e.g. 403 from a Policy (Laravel turns those into
        // HTTP exceptions first) or 419 when the CSRF cookie is missing.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($isJson, $error) {
            if ($isJson($request)) {
                $status = $e->getStatusCode();
                $code = match ($status) {
                    403 => 'FORBIDDEN',
                    419 => 'CSRF_TOKEN_MISMATCH',
                    default => 'HTTP_'.$status,
                };

                return $error($code, $e->getMessage() ?: 'Request failed.', $status);
            }
        });
    })->create();
