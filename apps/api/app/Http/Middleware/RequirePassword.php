<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Auth\Middleware\RequirePassword as BaseRequirePassword;
use Illuminate\Http\Request;

/**
 * "Enter your password again" before sensitive changes, such as turning two-factor
 * login on or off. Laravel's version answers in its own JSON shape; this one uses
 * ours, { "error": { "code": "PASSWORD_CONFIRMATION_REQUIRED", ... } }, with status 423.
 * After the user confirms, they are not asked again for 3 hours (auth.password_timeout).
 */
class RequirePassword extends BaseRequirePassword
{
    /**
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @param  string|int|null  $passwordTimeoutSeconds
     */
    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null): mixed
    {
        if ($request->expectsJson() && $this->shouldConfirmPassword($request, $passwordTimeoutSeconds)) {
            throw new ApiException('PASSWORD_CONFIRMATION_REQUIRED', 'Please enter your password again.', 423);
        }

        return parent::handle($request, $next, $redirectToRoute, $passwordTimeoutSeconds);
    }
}
