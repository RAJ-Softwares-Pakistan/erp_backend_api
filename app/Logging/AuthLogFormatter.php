<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\WebProcessor;

class AuthLogFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke($logger): void
    {
        // Create a JSON formatter with specific settings
        $formatter = new JsonFormatter(
            JsonFormatter::BATCH_MODE_JSON,
            true, // Include stack traces
            true, // Append newlines
            true  // Enable type handling for consistent output
        );
        
        // Use ISO 8601 format with microsecond precision and timezone
        $formatter->setDateFormat('Y-m-d\TH:i:s.uP');
        
        // Add standard processors
        $logger->pushProcessor(new ProcessIdProcessor());
        $logger->pushProcessor(new WebProcessor());
        
        // Add custom context processor for auth-specific data
        $logger->pushProcessor(function (LogRecord $record): LogRecord {
            $record->extra = array_merge($record->extra ?? [], [
                'environment' => config('app.env'),
                'app_name' => config('app.name'),
                'hostname' => gethostname(),
                'remote_ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
            
            // Add user context if available
            if ($user = Auth::user()) {
                $record->extra['user'] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            }
            
            return $record;
        });

        // Configure handlers
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);
            
            // Set smaller buffer size for auth events (20 records)
            if (method_exists($handler, 'setBufferSize')) {
                $handler->setBufferSize(20);
            }
            
            // Enable microsecond timestamp precision
            if (method_exists($handler, 'useMicrosecondTimestamps')) {
                $handler->useMicrosecondTimestamps(true);
            }
        }
    }
}
