<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * GET /api/health: the full check for the uptime monitor.
 * Returns 200 when the database and cache both respond, 503 when either is down.
 * (GET /up only says "the app is running" and is what the host's health check uses.)
 * "via" says whether the API recognised the web app (ADR-025): through courtku.vercel.app it
 * should say "browser", called directly "direct". A quick check after changing WEB_PROXY_SECRET.
 */
class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $database = $this->check(fn () => DB::select('select 1'));

        // When the cache is stored in the database, a dead database means a dead cache.
        // Skip asking again, so the answer comes back in seconds instead of timing out.
        $cacheUsesDatabase = config('cache.stores.'.config('cache.default').'.driver') === 'database';
        $cache = ($cacheUsesDatabase && ! $database)
            ? false
            : $this->check(fn () => Cache::store()->get('health-check'));

        $checks = ['database' => $database, 'cache' => $cache];
        $healthy = $database && $cache;

        return response()->json(
            ['status' => $healthy ? 'ok' : 'down', 'checks' => $checks, 'via' => $request->attributes->get('via')],
            $healthy ? 200 : 503,
        );
    }

    private function check(callable $probe): bool
    {
        try {
            $probe();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
