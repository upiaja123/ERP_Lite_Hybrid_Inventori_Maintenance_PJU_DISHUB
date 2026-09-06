<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request with Rate Limiting
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $key = 'api_rate_limit:' . ($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'error'       => 'Rate limit exceeded',
                'message'     => "Terlalu banyak request. Silakan coba lagi dalam {$seconds} detik.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));

        return $response;
    }
}
