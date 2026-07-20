<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Ensure uploaded files (profile photos, etc.) are publicly reachable.
        if (!app()->runningInConsole() && !file_exists(public_path('storage'))) {
            try {
                if (!file_exists(storage_path('app/public'))) {
                    mkdir(storage_path('app/public'), 0755, true);
                }
                symlink(storage_path('app/public'), public_path('storage'));
            } catch (\Throwable $e) {
                // Ignore — artisan storage:link can still be run manually.
            }
        }

        // Never force the root URL from the request Host header — that enables
        // host-header injection (forged password-reset / signed links). Use APP_URL.
        $appUrl = (string) config('app.url');
        $isSecureAppUrl = str_starts_with($appUrl, 'https://');
        $isTunnelAppUrl = str_contains($appUrl, 'trycloudflare.com') || str_contains($appUrl, 'onrender.com');

        if ($isSecureAppUrl || $isTunnelAppUrl || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
