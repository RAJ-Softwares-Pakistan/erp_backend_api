<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggingMiddleware
{
    private const DEFAULT_MAX_CONTENT_SIZE = 5120; // 5KB default for response content
    private const SENSITIVE_HEADERS = [
        'authorization',
        'cookie',
        'x-xsrf-token',
        'password',
        'password_confirmation',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Generate request ID and start timer
            $requestId = (string) Str::uuid();
            $startTime = microtime(true);
            
            // Add request ID to response headers for correlation
            $request->headers->set('X-Request-ID', $requestId);
            
            // Get route information
            $route = $request->route();
            $routeInfo = $route ? [
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'parameters' => $route->parameters(),
            ] : null;
        
            // Log the incoming request
            Log::channel('api')->info('API Request', [
                'request_id' => $requestId,
                'timestamp' => now()->format('Y-m-d\TH:i:s.uP'),
                'environment' => config('app.env'),
                'service' => config('app.name'),
                'type' => 'request',
                'http' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'protocol' => $request->server('SERVER_PROTOCOL'),
                    'query' => $request->query(),
                    'headers' => $this->formatHeaders($request->headers->all()),
                    'body' => $this->formatRequestBody($request),
                ],
                'route' => $routeInfo,
                'client' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->header('referer'),
                ],
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
                'meta' => [
                    'host' => gethostname(),
                    'pid' => getmypid(),
                ]
            ]);

            // Process the request
            $response = $next($request);            // Calculate request duration
            $duration = (microtime(true) - $startTime) * 1000;

            // Get logging configuration
            $config = config('logging.channels.api.response_logging', [
                'enabled' => false,
                'include_headers' => false,
                'max_content_size' => self::DEFAULT_MAX_CONTENT_SIZE,
            ]);

            // Build response log
            $logData = [
                'request_id' => $requestId,
                'type' => 'response',
                'duration_ms' => round($duration, 2),
                'http' => [
                    'status' => $response->status(),
                ],
            ];

            // Only include response details if enabled
            if ($config['enabled']) {
                if ($config['include_headers']) {
                    $logData['http']['headers'] = $this->formatHeaders($response->headers->all());
                }
                
                // Only log body for non-success responses or if explicitly configured
                if ($response->status() >= 400 || env('API_LOG_ALL_RESPONSES', false)) {
                    $logData['http']['body'] = $this->formatResponseBody($response, $config['max_content_size']);
                }
            }

            // Log the response
            Log::channel('api')->info('API Response', $logData);

            // Add request ID to response headers
            $response->headers->set('X-Request-ID', $requestId);

            return $response;
        } catch (\Throwable $e) {
            // Log error details
            Log::channel('error')->error('API Error', [
                'request_id' => $requestId ?? Str::uuid(),
                'timestamp' => now()->format('Y-m-d\TH:i:s.uP'),
                'type' => 'error',
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
                'http' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                ],
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'email' => $request->user()->email,
                ] : null,
            ]);

            throw $e;
        }
    }

    /**
     * Format request/response headers for logging
     */
    private function formatHeaders(array $headers): array
    {
        return collect($headers)
            ->mapWithKeys(function ($value, $key) {
                return [strtolower($key) => $value];
            })
            ->filter(function ($value, $key) {
                return !in_array($key, self::SENSITIVE_HEADERS);
            })
            ->map(function ($value) {
                return is_array($value) ? implode(', ', $value) : $value;
            })
            ->toArray();
    }

    /**
     * Format request body for logging, excluding sensitive data
     */
    private function formatRequestBody(Request $request): array
    {        $content = $request->getContent();
        $contentLength = strlen($content);

        if ($contentLength > self::DEFAULT_MAX_CONTENT_SIZE) {
            return [
                'content_length' => $contentLength,
                'content_truncated' => true,
            ];
        }

        $input = $request->except(['password', 'password_confirmation', 'current_password', 'new_password']);
        return collect($input)->toArray();
    }    /**
     * Format response body for logging
     * 
     * @param mixed $response The response object to format
     * @param int $maxSize Maximum content size in bytes
     * @return array|string Formatted response body
     */
    private function formatResponseBody($response, int $maxSize = self::DEFAULT_MAX_CONTENT_SIZE): array|string
    {
        try {
            $content = method_exists($response, 'getData')
                ? json_encode($response->getData(true))
                : $response->getContent();

            $contentLength = strlen($content);

            // Return only content length if content exceeds max size
            if ($contentLength > $maxSize) {
                return [
                    'content_length' => $contentLength,
                    'content_truncated' => true,
                ];
            }

            // Try to parse JSON responses
            if (is_string($content) && $this->isJson($content)) {
                return json_decode($content, true) ?? $content;
            }

            return $content;
        } catch (\Throwable $e) {
            return [
                'content' => '[Error formatting response body: ' . $e->getMessage() . ']',
            ];
        }
    }

    /**
     * Check if a string is valid JSON
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
