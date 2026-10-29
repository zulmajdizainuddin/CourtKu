<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The "reset your password" email links to the web app's page, which then
        // sends the new password to POST /reset-password.
        ResetPassword::createUrlUsing(fn ($user, string $token) => config('app.frontend_url')
            .'/reset-password?'.http_build_query(['token' => $token, 'email' => $user->getEmailForPasswordReset()]));

        // The platform admin approves and suspends venues (routes/api.php, "can:admin").
        Gate::define('admin', fn (User $user) => $user->is_platform_admin);
    }
}
