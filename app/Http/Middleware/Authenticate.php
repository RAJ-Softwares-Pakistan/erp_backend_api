<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        Log::channel('auth')->warning('Unauthenticated access attempt', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ]);

        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards
        );
    }

    /**
     * Remove sensitive information from headers before logging
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-xsrf-token'];
        return collect($headers)
            ->mapWithKeys(function ($value, $key) {
                return [strtolower($key) => $value];
            })
            ->filter(function ($value, $key) use ($sensitiveHeaders) {
                return !in_array($key, $sensitiveHeaders);
            })
            ->map(function ($value) {
                return is_array($value) ? implode(', ', $value) : $value;
            })
            ->toArray();
    }
} 