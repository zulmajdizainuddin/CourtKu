<?php

namespace App\Http\Controllers;

use App\Services\Housekeeping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/tick  (header X-Cron-Secret: <CRON_SECRET>)
 *
 * Render's free plan has no scheduler and no background worker, so a free cron service calls
 * this every minute to run the every-minute jobs (ADR-022). Only a caller that knows the secret
 * can run them. Without a CRON_SECRET set, the endpoint doesn't exist (404).
 */
class TickController extends Controller
{
    public function __invoke(Request $request, Housekeeping $housekeeping): JsonResponse
    {
        $secret = (string) config('services.cron.secret');
        abort_if($secret === '', 404);
        // hash_equals takes the same time whether the first or last character is wrong, so the secret can't be guessed bit by bit.
        abort_unless(hash_equals($secret, (string) $request->header('X-Cron-Secret')), 403);

        return response()->json(['data' => $housekeeping->run()]);
    }
}
