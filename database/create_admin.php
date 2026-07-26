<?php

declare(strict_types=1);

/**
 * Creates an administrator account.
 *
 * Usage:
 *   php database/create_admin.php "Full Name" you@example.com
 *
 * The password is prompted for and hidden as you type, so it never lands in
 * your shell history. For scripted setup, pass it through the environment
 * instead — also history-free:
 *
 *   ADMIN_PASSWORD='…' php database/create_admin.php "Full Name" you@example.com
 */

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

require __DIR__ . '/../app/bootstrap.php';

use App\Models\User;

const MIN_PASSWORD_LENGTH = 12;

/**
 * Write a message to stderr and stop with a failing exit code.
 */
function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$name  = $argv[1] ?? null;
$email = $argv[2] ?? null;

if ($name === null || $email === null) {
    fail("Usage: php database/create_admin.php \"Full Name\" you@example.com");
}

$name  = trim($name);
$email = strtolower(trim($email));

if ($name === '') {
    fail('Name cannot be empty.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail("'{$email}' is not a valid email address.");
}

$users = new User();

if ($users->emailExists($email)) {
    fail("An account already exists for {$email}.");
}

// ── Collect the password ────────────────────────────────────────────────────
$password = getenv('ADMIN_PASSWORD');

if (is_string($password) && $password !== '') {
    $confirm = $password;
} else {
    // Hiding is only possible on a real terminal; piped input still works, it
    // just has nothing to hide.
    $interactive = stream_isatty(STDIN);
    $hidden      = $interactive && hideInput();

    // Always put the terminal back, even on Ctrl+C or a fatal error — otherwise
    // the shell is left with echo off and stops showing what you type.
    register_shutdown_function(static function () use ($hidden): void {
        if ($hidden) {
            showInput();
        }
    });

    if (function_exists('pcntl_signal')) {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, static function (): never {
            showInput();
            fwrite(STDERR, PHP_EOL . 'Cancelled. No account was created.' . PHP_EOL);
            exit(130);
        });
    }

    if ($interactive) {
        echo 'Creating an admin account for ' . $email . PHP_EOL;
        echo $hidden
            ? '(your password is hidden as you type — nothing will appear, that is expected)' . PHP_EOL
            : 'WARNING: this terminal will show your password as you type.' . PHP_EOL;
    }

    $password = prompt(sprintf('Password (at least %d characters): ', MIN_PASSWORD_LENGTH));
    $confirm  = prompt('Confirm password: ');

    if ($hidden) {
        showInput();
    }
}

// ── Validate ────────────────────────────────────────────────────────────────
if ($password === '') {
    fail('No password entered. Nothing was created.');
}

if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
    fail(sprintf(
        'Password is %d characters; it must be at least %d. Nothing was created.',
        mb_strlen($password),
        MIN_PASSWORD_LENGTH
    ));
}

if ($password !== $confirm) {
    fail('The two passwords did not match. Nothing was created.');
}

// ── Create ──────────────────────────────────────────────────────────────────
$id = $users->create([
    'name'      => $name,
    'email'     => $email,
    'password'  => $password,
    'role'      => 'admin',
    'is_active' => 1,
]);

printf('Administrator #%d created for %s.%s', $id, $email, PHP_EOL);
printf('Sign in at %s/admin/login%s', App\Core\Config::get('app.url'), PHP_EOL);

/**
 * Read one line from stdin, without the trailing newline.
 */
function prompt(string $label): string
{
    echo $label;
    $line = fgets(STDIN);
    echo PHP_EOL;

    return $line === false ? '' : trim($line);
}

/**
 * Turn off terminal echo. Returns false if it could not be done.
 */
function hideInput(): bool
{
    if (!function_exists('shell_exec')) {
        return false;
    }

    shell_exec('stty -echo 2>/dev/null');

    // Confirm it actually took effect rather than assuming.
    return str_contains((string) shell_exec('stty -a 2>/dev/null'), '-echo');
}

function showInput(): void
{
    if (function_exists('shell_exec')) {
        shell_exec('stty echo 2>/dev/null');
    }
}
