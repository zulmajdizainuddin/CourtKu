<?php

namespace App\Providers;

use App\Http\Middleware\TrustWebProxy;
use App\Models\User;
use App\Services\Cloudinary;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        // On the live site, never email @example.com or its subdomains: they are reserved for examples
        // (the demo accounts, ADR-030) and can't receive mail, so sending would only waste Brevo's allowance.
        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->channel === 'mail' && $this->app->isProduction() && self::isExampleAddress($event->notifiable)) {
                return false;
            }
        });

        // The platform admin approves and suspends venues (routes/api.php, "can:admin").
        Gate::define('admin', fn (User $user) => $user->is_platform_admin);

        // Public venue pages and times (plan: 60 a minute each). The web app's own server builds the
        // public pages for everyone from one address, so it gets a bucket of its own instead of
        // sharing a visitor's 60 (ADR-025). Pages are cached for a minute, so 600 is plenty.
        RateLimiter::for('public-reads', fn (Request $request) => $request->attributes->get('via') === TrustWebProxy::VIA_WEB_SERVER
            ? Limit::perMinute(600)->by('web-server')
            : Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }

    /** Whether a notification would go to example.com or one of its subdomains. */
    public static function isExampleAddress(object $notifiable): bool
    {
        $address = $notifiable instanceof AnonymousNotifiable
            ? $notifiable->routes['mail'] ?? null
            : ($notifiable->email ?? null);
        $domain = strtolower((string) substr(strrchr((string) $address, '@') ?: '', 1));

        return $domain === 'example.com' || str_ends_with($domain, '.example.com');
    }
}
