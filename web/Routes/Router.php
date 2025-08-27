<?php
namespace Routes;

use App\Middlewares\Middleware;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$ROUTES = ['GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => []];

/**
 * Route helpers. $middleware can be an array of callables or names registered via register_middleware.
 */
function get(string $path, $handler, array $middleware = []): void    { route('GET', $path, $handler, $middleware); }
function post(string $path, $handler, array $middleware = []): void   { route('POST', $path, $handler, $middleware); }
function put(string $path, $handler, array $middleware = []): void    { route('PUT', $path, $handler, $middleware); }
function patch(string $path, $handler, array $middleware = []): void  { route('PATCH', $path, $handler, $middleware); }
function delete(string $path, $handler, array $middleware = []): void { route('DELETE', $path, $handler, $middleware); }

function route(string $method, string $path, $handler, array $middleware = []): void {
    global $ROUTES;
    $ROUTES[$method][normalize_path($path)] = ['handler' => $handler, 'middleware' => $middleware];
}

function normalize_path(string $path): string {
    $p = '/' . ltrim($path, '/');
    return rtrim($p, '/') ?: '/';
}

function redirect(string $to, int $code = 302): void {
    http_response_code($code);
    header('Location: ' . $to);
    exit;
}

function dispatch(): void {
    global $ROUTES;

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $uri = normalize_path($uri);

    $route = $ROUTES[$method][$uri] ?? null;
    if ($route === null) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }

    // Resolve middleware: merge globals + route-specific
    $mwList = array_merge(Middleware::$GLOBAL_MIDDLEWARE, $route['middleware'] ?? []);
    $resolved = [];
    foreach ($mwList as $mw) {
        if (is_string($mw)) {
            if (!isset(Middleware::$NAMED_MIDDLEWARE[$mw])) {
                http_response_code(500);
                echo "Unknown middleware: {$mw}";
                return;
            }
            $resolved[] = Middleware::$NAMED_MIDDLEWARE[$mw];
        } elseif (is_callable($mw)) {
            $resolved[] = $mw;
        } else {
            http_response_code(500);
            echo 'Invalid middleware';
            return;
        }
    }

    // Final handler as a closure
    $final = function() use ($route): void {
        $handler = $route['handler'];
        if (is_callable($handler)) {
            $handler();
            return;
        }
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                [$class, $method] = explode('@', $handler, 2);
                
                // Handle namespaced controllers
                if (strpos($class, '\\') === false) {
                    // If no namespace provided, assume App\Controllers namespace
                    $class = 'App\\Controllers\\' . $class;
                }
                
                // No need to manually include the file with PSR-4
                if (!class_exists($class)) { 
                    http_response_code(500); 
                    echo "Controller class not found: $class"; 
                    return; 
                }
                
                $instance = new $class();
                if (!method_exists($instance, $method)) { 
                    http_response_code(500); 
                    echo "Controller method not found: $class@$method"; 
                    return; 
                }
                
                $instance->$method();
                return;
            }
            
            $file = __DIR__ . '/' . ltrim($handler, '/');
            if (substr($file, -4) !== '.php') { $file .= '.php'; }
            if (!is_file($file)) { http_response_code(500); echo "File not found: $file"; return; }
            include_once $file;
            return;
        }
        http_response_code(500);
        echo 'Invalid route handler';
    };

    // Build middleware pipeline: each middleware receives callable $next
    $runner = array_reduce(
        array_reverse($resolved),
        function(callable $next, callable $mw): callable {
            return function() use ($mw, $next): void {
                // Middleware should call $next() to continue or end the response to stop.
                $mw($next);
            };
        },
        $final
    );

    $runner();
}
