<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class BruteForceProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw new ThrottleRequestsException(
                'Too many attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.', null, [429]
            );
        }

        RateLimiter::hit($key, 180);

        return $next($request);
    }

    /**
     * Get the rate limiting key for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        $email = $request->input('email', 'guest');
        return Str::lower($email) . '|' . $request->ip();
    }
}
