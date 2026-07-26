<?php

declare(strict_types=1);

/**
 * Test runner.
 *
 *   php tests/run.php            quiet — only failures are printed
 *   VERBOSE=1 php tests/run.php  print every assertion
 *
 * No database is required: the suites prime the settings cache and pass
 * fixtures straight into the views. Exits non-zero if anything fails, which is
 * what gates the deploy workflow.
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

$root = dirname(__DIR__);

// bootstrap.php requires a .env; in CI there is none, so fall back to the
// example file, which points at a database nothing here actually touches.
if (!is_file($root . '/.env')) {
    copy($root . '/.env.example', $root . '/.env');
    register_shutdown_function(static fn () => @unlink($root . '/.env'));
}

require $root . '/app/bootstrap.php';
require __DIR__ . '/Harness.php';

use Tests\Harness;

echo 'PHP ' . PHP_VERSION . PHP_EOL;

foreach (['unit', 'render'] as $suite) {
    echo PHP_EOL . '━━ ' . $suite . ' ━━' . PHP_EOL;
    require __DIR__ . '/' . $suite . '.php';
}

exit(Harness::summary());
