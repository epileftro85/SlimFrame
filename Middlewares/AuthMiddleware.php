<?php
namespace App\Middlewares;

class AuthMiddleware extends Middleware {
    public static function register(): void {
        Middleware::register_middleware('auth', function (callable $next): void {
            if (empty($_SESSION['uid'])) {
                \Routes\redirect('/', 302);
                return;
            }
            $next();
        });
    }
}
