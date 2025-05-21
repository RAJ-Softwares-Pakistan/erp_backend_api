<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LogRotationManager
{
    /**
     * Maximum size for log files before rotation (in bytes)
     * Default: 100MB
     */
    private const MAX_LOG_SIZE = 104857600;

    /**
     * Number of days to keep rotated logs
     */
    private int $retentionDays;

    /**
     * Directory paths to monitor
     */
    private array $logDirectories = [
        'api' => 'logs/api',
        'auth' => 'logs/auth',
        'errors' => 'logs/errors',
        'system' => 'logs/system',
    ];

    public function __construct()
    {
        $this->retentionDays = (int) config('logging.days', 7);
    }

    /**
     * Run the log rotation and cleanup process
     */
    public function handle(): void
    {
        try {
            foreach ($this->logDirectories as $type => $directory) {
                $this->processDirectory($type, storage_path($directory));
            }
            $this->cleanup();
        } catch (\Throwable $e) {
            Log::error('Log rotation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process a single log directory
     */
    private function processDirectory(string $type, string $path): void
    {
        if (!File::isDirectory($path)) {
            return;
        }

        $files = File::files($path);
        foreach ($files as $file) {
            if ($file->getSize() > self::MAX_LOG_SIZE) {
                $this->rotateFile($file->getPathname());
            }
        }
    }

    /**
     * Rotate a single log file
     */
    private function rotateFile(string $filepath): void
    {
        $timestamp = Carbon::now()->format('Y-m-d-His');
        $newPath = $filepath . '.' . $timestamp;

        try {
            File::move($filepath, $newPath);
            File::put($filepath, ''); // Create new empty log file
            chmod($filepath, 0640);
        } catch (\Throwable $e) {
            Log::error('Failed to rotate log file', [
                'file' => $filepath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up old log files
     */
    private function cleanup(): void
    {
        $cutoff = Carbon::now()->subDays($this->retentionDays);

        foreach ($this->logDirectories as $directory) {
            $path = storage_path($directory);
            if (!File::isDirectory($path)) {
                continue;
            }

            collect(File::files($path))
                ->filter(function ($file) use ($cutoff) {
                    // Only delete rotated files older than retention period
                    return $file->getMTime() < $cutoff->timestamp
                        && preg_match('/\.\d{4}-\d{2}-\d{2}-\d{6}$/', $file->getFilename());
                })
                ->each(function ($file) {
                    try {
                        File::delete($file->getPathname());
                    } catch (\Throwable $e) {
                        Log::error('Failed to delete old log file', [
                            'file' => $file->getPathname(),
                            'error' => $e->getMessage()
                        ]);
                    }
                });
        }
    }
}
