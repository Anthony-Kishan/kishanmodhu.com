<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Minimal .env loader.
 *
 * Values are parsed once and cached in a static map rather than exported to
 * $_ENV/putenv(), so configuration cannot leak into subprocess environments.
 */
final class Env
{
    /** @var array<string, string>|null */
    private static ?array $values = null;

    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            throw new RuntimeException(
                "Environment file not found at {$path}. Copy .env.example to .env and fill it in."
            );
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip a single matching pair of surrounding quotes.
            if (strlen($value) > 1) {
                $first = $value[0];
                if (($first === '"' || $first === "'") && str_ends_with($value, $first)) {
                    $value = substr($value, 1, -1);
                }
            }

            $values[$key] = $value;
        }

        self::$values = $values;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$values === null || !array_key_exists($key, self::$values)) {
            return $default;
        }

        $value = self::$values[$key];

        return match (strtolower($value)) {
            'true'  => true,
            'false' => false,
            'null', '' => $default,
            default => $value,
        };
    }
}
