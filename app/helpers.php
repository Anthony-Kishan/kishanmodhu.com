<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

/**
 * Global template helpers.
 *
 * Kept deliberately small: escaping, URL building and flash access are the only
 * things every view needs.
 */

if (!function_exists('e')) {
    /**
     * Escape a value for HTML output. Every dynamic value in a template goes
     * through this unless it is explicitly known-safe markup.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    /**
     * Build a URL for a file under /public, with a cache-busting fingerprint.
     */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $full = Config::get('app.public_path') . '/' . $path;
        $url  = '/' . $path;

        if (is_file($full)) {
            $url .= '?v=' . filemtime($full);
        }

        return $url;
    }
}

if (!function_exists('upload_url')) {
    /**
     * Resolve a stored media path to a public URL, tolerating empty values.
     */
    function upload_url(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return Config::get('app.url') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::old($key, $default);
    }
}

if (!function_exists('errors')) {
    /**
     * @return array<string, string>
     */
    function errors(): array
    {
        static $errors = null;

        if ($errors === null) {
            $errors = Session::pullFlash('errors', []);
        }

        return $errors;
    }
}

if (!function_exists('error_for')) {
    function error_for(string $field): ?string
    {
        return errors()[$field] ?? null;
    }
}

if (!function_exists('comma_lines')) {
    /**
     * Split a comma-separated string into trimmed parts.
     *
     * Used where the original markup had a hard-coded <br> and the value is now
     * editable, e.g. "LET'S BUILD YOUR,WEB IDENTITY".
     *
     * @return array<int, string>
     */
    function comma_lines(string $value): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            static fn (string $part): bool => $part !== ''
        ));
    }
}
