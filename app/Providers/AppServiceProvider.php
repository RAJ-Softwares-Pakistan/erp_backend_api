<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\URL;
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
        Scramble::routes(function ($route) {
            return $route->name('api.');
        });

        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        Gate::define('viewApiDocs', function () {
            return true;
        });
    }
}
