<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Storage;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->trustProxies(at: '*');
        $appUrl = env('APP_URL');
        $middleware->trustHosts(at: $appUrl ? [parse_url($appUrl, PHP_URL_HOST)] : []);
        
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // API middleware group
        $middleware->group('api', [
            \App\Http\Middleware\ApiLoggingMiddleware::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Named middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withProviders([
        // Essential Framework Providers
        \Illuminate\Auth\AuthServiceProvider::class,        // For authentication
        \Illuminate\Database\DatabaseServiceProvider::class, // For database operations
        \Illuminate\Encryption\EncryptionServiceProvider::class, // For encrypting cookies/sessions
        \Illuminate\Hashing\HashServiceProvider::class,     // For password hashing
        \Illuminate\Validation\ValidationServiceProvider::class, // For request validation
        
        // Application Providers
        \App\Providers\AppServiceProvider::class,           // App bootstrapping
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,         // API routing & rate limiting
    ])
    ->withSchedule(function ($schedule) {
        // Log rotation - runs daily at midnight
        $schedule->command('logs:rotate')
            ->daily()
            ->at('00:00')
            ->environments(['production', 'staging']);

        // Log size check - runs every 6 hours
        $schedule->call(function () {
            $logFiles = [
                storage_path('logs/api/api.log'),
                storage_path('logs/auth/auth.log'),
                storage_path('logs/errors/error.log'),
                storage_path('logs/system/laravel.log'),
            ];

            foreach ($logFiles as $file) {
                if (file_exists($file) && filesize($file) > 104857600) { // 100MB
                    \Illuminate\Support\Facades\Artisan::call('logs:rotate');
                    break;
                }
            }
        })->everyMinute()->environments(['production', 'staging']);

        // Clean old log files - runs weekly
        $schedule->command('logs:rotate')->weekly();
    })
    ->withCommands([
        // Only include our custom commands directory
        __DIR__.'/../app/Console/Commands',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
