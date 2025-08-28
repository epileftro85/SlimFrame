<?php
namespace App\Middlewares;

class Middleware
{
    public static array $GLOBAL_MIDDLEWARE = [];
    public static array $NAMED_MIDDLEWARE = [];

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Register a named middleware for reuse: register_middleware('auth', function(callable $next){ ... })
     */
    protected static function register_middleware(string $name, callable $middleware): void
    {
        self::$NAMED_MIDDLEWARE[$name] = $middleware;
    }

    /**
     * Add global middleware applied to every route.
     */
    protected static function use_middleware(callable $middleware): void
    {
        self::$GLOBAL_MIDDLEWARE[] = $middleware;
    }
}
