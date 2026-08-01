<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;

/**
 * Application bootstrap: autoloading, error handling, environment, session.
 *
 * Error handling is installed *before* anything that can fail, so a missing or
 * misplaced .env produces a logged, readable message rather than a blank 500.
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

// ── Error handling ───────────────────────────────────────────────────────────
// Installed first, with a log path derived from the filesystem rather than
// config, so failures during configuration loading are still recorded.
$logDirectory = $root . '/storage/logs';

if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0755, true);
}

error_reporting(E_ALL);
ini_set('log_errors', '1');

if (is_dir($logDirectory) && is_writable($logDirectory)) {
    ini_set('error_log', $logDirectory . '/php-error.log');
}

// Flipped to the configured value once config is available; the handler reads
// it by reference so the early registration picks up the change.
$debug = false;
ini_set('display_errors', '0');

set_exception_handler(static function (Throwable $e) use (&$debug): void {
    error_log(sprintf(
        '[%s] %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

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

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $e;

        return;
    }

    // Fall back to plain text if even the error template cannot be rendered —
    // which is likely when the failure is in configuration itself.
    try {
        echo View::render('errors/500', ['pageTitle' => 'Error'], 'layouts/minimal');
    } catch (Throwable) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Something went wrong. Please try again later.';
    }
});

// ── Environment & configuration ──────────────────────────────────────────────
Env::load($root . '/.env');
Config::setPath($root . '/config');
View::setPath($root . '/app/Views');

$debug = (bool) Config::get('app.debug', false);
ini_set('display_errors', $debug ? '1' : '0');

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

// ── Session ──────────────────────────────────────────────────────────────────
Session::start();
