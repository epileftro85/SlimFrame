<?php
namespace Routes;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__.'/Router.php';

use App\Middlewares\AuthMiddleware;

AuthMiddleware::register();
