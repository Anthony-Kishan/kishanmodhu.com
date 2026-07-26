<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session wrapper with hardened cookie defaults and flash-message support.
 */
final class Session
{
    /**
     * Flash payload written by the *previous* request.
     *
     * Lifted out of the session as soon as the session starts and held here for
     * the duration of this request, so a flash always survives exactly one
     * redirect and never leaks into the request after that.
     *
     * @var array<string, mixed>
     */
    private static array $incomingFlash = [];

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('kmsession');
        session_start();

        self::$incomingFlash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Guard against session fixation after a privilege change.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Drop every value but keep the session usable.
     *
     * Used on sign-out in preference to destroy(): the old session ID is
     * invalidated, but a flash message set immediately afterwards still
     * survives the redirect to the login screen.
     */
    public static function flush(): void
    {
        $_SESSION = [];
        self::$incomingFlash = [];
        self::regenerate();
    }

    /**
     * Store a value for the next request only.
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pullFlash(string $key, mixed $default = null): mixed
    {
        $value = self::$incomingFlash[$key] ?? $default;
        unset(self::$incomingFlash[$key]);

        return $value;
    }

    /**
     * Persist submitted input so a failed form can be re-rendered with values intact.
     *
     * @param array<string, mixed> $input
     */
    public static function flashInput(array $input): void
    {
        unset($input['password'], $input['password_confirmation'], $input['_token'], $input['_method']);
        self::flash('_old', $input);
    }

    public static function old(string $key, mixed $default = null): mixed
    {
        return self::$incomingFlash['_old'][$key] ?? $default;
    }
}
