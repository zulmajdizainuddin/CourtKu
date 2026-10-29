<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Owners and the platform admin must use two-factor login (plan section 11):
 * a stolen password alone must never be enough to run a venue or the platform.
 *
 * "two-factor"         only users who need it (admin, or owner of any venue)
 * "two-factor:always"  everyone, e.g. before listing a venue, which makes you an owner
 */
class RequireTwoFactor
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $when = 'if-required'): Response
    {
        $user = $request->user();

        // Checked first because it needs no database query.
        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        if ($when === 'always' || $user->requiresTwoFactor()) {
            throw new ApiException('TWO_FACTOR_REQUIRED', 'Turn on two-factor login to continue.', 403);
        }

        return $next($request);
    }
}
