<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Cloudinary;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One Cloudinary client for the whole app, filled from config/services.php.
        $this->app->singleton(Cloudinary::class, fn () => new Cloudinary(
            config('services.cloudinary.cloud_name'),
            config('services.cloudinary.api_key'),
            config('services.cloudinary.api_secret'),
            (string) config('services.cloudinary.folder'),
        ));

        // Local development on Windows (ADR-011): `php artisan serve` starts PHP without
        // Windows' TEMP and TMP settings, so PHP has nowhere to put uploaded files and every
        // photo upload fails with "unable to create a temporary file". Pass them on.
        // Production runs PHP-FPM, not artisan serve, so this changes nothing there.
        ServeCommand::$passthroughVariables = array_values(array_unique([...ServeCommand::$passthroughVariables, 'TEMP', 'TMP']));
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
