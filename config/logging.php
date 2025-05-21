<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\HostnameProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */    
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'daily,api,auth,error')),
            'ignore_exceptions' => false,
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_RETENTION_DAYS', 30),
            'replace_placeholders' => true,
            'permission' => 0640,
            'locking' => true,
            'processors' => [
                PsrLogMessageProcessor::class,
                WebProcessor::class,
                MemoryUsageProcessor::class,
                IntrospectionProcessor::class,
                HostnameProcessor::class,
            ],
        ],

        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/api.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_RETENTION_DAYS', 30),
            'replace_placeholders' => true,
            'permission' => 0640,
            'locking' => true,
            'tap' => [\App\Logging\ApiLogFormatter::class],
            'processors' => [
                PsrLogMessageProcessor::class,
                WebProcessor::class,
            ],
            'response_logging' => [
                'enabled' => env('API_LOG_RESPONSES', false),
                'include_headers' => env('API_LOG_RESPONSE_HEADERS', false),
                'max_content_size' => env('API_LOG_MAX_CONTENT_SIZE', 5120), // 5KB default
            ],
        ],

        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/auth.log'),
            'level' => 'info',
            'days' => env('LOG_RETENTION_DAYS', 30),
            'replace_placeholders' => true,
            'permission' => 0640,
            'locking' => true,
            'tap' => [\App\Logging\AuthLogFormatter::class],
            'processors' => [
                PsrLogMessageProcessor::class,
                WebProcessor::class,
                IntrospectionProcessor::class,
                HostnameProcessor::class,
            ],
        ],

        'error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors/error.log'),
            'level' => 'error',
            'days' => env('LOG_RETENTION_DAYS', 30),
            'replace_placeholders' => true,
            'permission' => 0640,
            'locking' => true,
            'processors' => [
                PsrLogMessageProcessor::class,
                WebProcessor::class,
                MemoryUsageProcessor::class,
                IntrospectionProcessor::class,
                HostnameProcessor::class,
            ],
        ],

        'system' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system/system.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => env('LOG_RETENTION_DAYS', 30),
            'replace_placeholders' => true,
            'permission' => 0640,
            'locking' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':warning:'),
            'level' => env('LOG_SLACK_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        // For development
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        // For containerized environments
        'stdout' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stdout',
            ],
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
            'level' => 'emergency',
        ],

    ],

];
