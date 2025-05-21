<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // For login and register endpoints, use a stricter rate limit
            if ($request->is('api/login') || $request->is('api/register')) {
                return Limit::perMinute(5)->by($request->ip());
            }
            
            // For other API endpoints, use a more lenient rate limit
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
