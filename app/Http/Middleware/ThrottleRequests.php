<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$limits
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$limits): Response
    {
        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($key = $this->resolveRequestSignature($request), $limit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            $this->limiter->hit($key, 60); // 60 seconds
        }

        return $next($request);
    }

    /**
     * Resolve request signature for rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request): string
    {
        return sha1(
            $request->method() . '|' .
            $request->host() . '|' .
            $request->ip()
        );
    }
}