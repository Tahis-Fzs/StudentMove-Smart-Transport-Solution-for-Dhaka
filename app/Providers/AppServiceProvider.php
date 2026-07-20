<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $appUrl = (string) config('app.url');
        $isTunnel = str_contains($appUrl, 'trycloudflare.com') || str_contains($appUrl, 'onrender.com');
        if ($isTunnel || (app()->environment('production') && str_starts_with($appUrl, 'https://'))) {
            URL::forceScheme('https');
        }
    }
}
