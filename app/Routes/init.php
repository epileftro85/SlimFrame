<?php
namespace Routes;

require ROOT_PATH . 'vendor/autoload.php';
require_once __DIR__.'/Router.php';

use App\Middlewares\AuthMiddleware;

AuthMiddleware::register();
