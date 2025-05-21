<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\WebProcessor;

class ApiLogFormatter
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
          // Add minimal context processor
        $logger->pushProcessor(function (LogRecord $record): LogRecord {
            $record->extra = array_merge($record->extra ?? [], [
                'environment' => config('app.env'),
            ]);
            return $record;
        });

        // Configure handlers
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);
            
            // Set buffer size to 50 records for batch processing
            if (method_exists($handler, 'setBufferSize')) {
                $handler->setBufferSize(50);
            }
            
            // Enable microsecond timestamp precision
            if (method_exists($handler, 'useMicrosecondTimestamps')) {
                $handler->useMicrosecondTimestamps(true);
            }
        }
    }
}
