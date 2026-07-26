<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Per-session CSRF token, compared in constant time.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::put(self::KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::KEY);
    }

    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        return hash_equals(self::token(), $token);
    }

    public static function field(): string
    {
        return sprintf('<input type="hidden" name="_token" value="%s">', htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Rotate the token, e.g. after login.
     */
    public static function rotate(): void
    {
        Session::forget(self::KEY);
    }
}
