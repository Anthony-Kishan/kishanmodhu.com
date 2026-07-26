<?php

declare(strict_types=1);

/**
 * Front controller — the single entry point for every request.
 *
 * Only this directory is exposed as the web root; application code, config and
 * the .env file all live one level up and cannot be requested directly.
 */

use App\Core\Request;

// PHP's built-in server (php -S localhost:8000 -t public public/index.php) has
// no .htaccess, so hand real files back to it directly. Apache/nginx never
// reach this branch.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . urldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../app/bootstrap.php';

/** @var App\Core\Router $router */
$router = require __DIR__ . '/../config/routes.php';

$router->dispatch(new Request())->send();
