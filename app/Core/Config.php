<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Read-only access to the arrays returned by the files in /config.
 *
 * Supports dot notation: Config::get('database.host').
 */
final class Config
{
    /** @var array<string, mixed> */
    private static array $loaded = [];

    private static string $path = '';

    public static function setPath(string $path): void
    {
        self::$path = rtrim($path, '/');
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if (!isset(self::$loaded[$file])) {
            $filePath = self::$path . '/' . $file . '.php';

            if (!is_readable($filePath)) {
                throw new RuntimeException("Config file [{$file}] not found.");
            }

            self::$loaded[$file] = require $filePath;
        }

        $value = self::$loaded[$file];

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
