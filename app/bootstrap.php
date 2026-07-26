<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;

/**
 * Application bootstrap: autoloading, environment, error handling, shared state.
 *
 * Returns nothing; the front controller takes over once this file completes.
 */

$root = dirname(__DIR__);

// ── PSR-4 autoloader for the App\ namespace ──────────────────────────────────
spl_autoload_register(static function (string $class) use ($root): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, 4));
    $file     = $root . '/app/' . $relative . '.php';

    if (is_readable($file)) {
        require_once $file;
    }
});

require_once $root . '/app/helpers.php';

// ── Environment & configuration ──────────────────────────────────────────────
Env::load($root . '/.env');
Config::setPath($root . '/config');
View::setPath($root . '/app/Views');

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

// ── Error handling ───────────────────────────────────────────────────────────
$debug = (bool) Config::get('app.debug', false);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', Config::get('app.log_path') . '/php-error.log');

set_exception_handler(static function (Throwable $e) use ($debug): void {
    error_log(sprintf('[%s] %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));

    // CLI scripts (seeding, admin creation) get a readable message on stderr
    // rather than a page of HTML.
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, PHP_EOL . 'Error: ' . $e->getMessage() . PHP_EOL);
        fwrite(STDERR, '  at ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL);

        if ($previous = $e->getPrevious()) {
            fwrite(STDERR, '  caused by: ' . $previous->getMessage() . PHP_EOL);
        }

        exit(1);
    }

    http_response_code(500);

    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $e;

        return;
    }

    // Fall back to plain text if even the error template cannot be rendered.
    try {
        echo View::render('errors/500', ['pageTitle' => 'Error'], 'layouts/minimal');
    } catch (Throwable) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Something went wrong. Please try again later.';
    }
});

// ── Session ──────────────────────────────────────────────────────────────────
Session::start();
