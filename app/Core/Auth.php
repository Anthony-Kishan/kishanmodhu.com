<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Session-backed authentication with throttled login attempts.
 */
final class Auth
{
    private const USER_KEY     = 'auth_user_id';
    private const LAST_SEEN    = 'auth_last_activity';
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT      = 900; // 15 minutes

    /** @var array<string, mixed>|null Request-scoped cache. */
    private static ?array $cachedUser = null;

    public static function attempt(string $email, string $password): bool
    {
        if (self::isLockedOut()) {
            return false;
        }

        $user = (new User())->findByEmail($email);

        // Always run a hash comparison so a missing account and a wrong
        // password take the same amount of time.
        $hash = $user['password'] ?? '$2y$10$OhQtCUOXNW1fsGIJtUbW3uFOC3qHh6mZnVyBfsvSco9OAKbnMDmUe';

        if (!password_verify($password, $hash) || $user === null || (int) $user['is_active'] !== 1) {
            self::recordFailure();

            return false;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            (new User())->updatePassword((int) $user['id'], $password);
        }

        self::clearFailures();
        Session::regenerate();
        Csrf::rotate();
        Session::put(self::USER_KEY, (int) $user['id']);
        Session::put(self::LAST_SEEN, time());
        (new User())->touchLastLogin((int) $user['id']);

        return true;
    }

    public static function check(): bool
    {
        if (!Session::has(self::USER_KEY)) {
            return false;
        }

        $idleTimeout = (int) Config::get('app.session_idle_timeout', 7200);
        $lastSeen    = (int) Session::get(self::LAST_SEEN, 0);

        if ($idleTimeout > 0 && (time() - $lastSeen) > $idleTimeout) {
            self::logout();

            return false;
        }

        Session::put(self::LAST_SEEN, time());

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $user = (new User())->find((int) Session::get(self::USER_KEY));

        if ($user === null) {
            self::logout();

            return null;
        }

        unset($user['password']);
        self::$cachedUser = $user;

        return $user;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function logout(): void
    {
        self::$cachedUser = null;

        // flush() rather than destroy(): the session stays usable so the
        // "please sign in" flash set by RequireAuth survives the redirect.
        Session::flush();
    }

    public static function isLockedOut(): bool
    {
        $attempts = (int) Session::get('login_attempts', 0);
        $lockedAt = (int) Session::get('login_locked_at', 0);

        if ($attempts < self::MAX_ATTEMPTS) {
            return false;
        }

        if ((time() - $lockedAt) > self::LOCKOUT) {
            self::clearFailures();

            return false;
        }

        return true;
    }

    public static function secondsUntilUnlock(): int
    {
        return max(0, self::LOCKOUT - (time() - (int) Session::get('login_locked_at', 0)));
    }

    private static function recordFailure(): void
    {
        $attempts = (int) Session::get('login_attempts', 0) + 1;
        Session::put('login_attempts', $attempts);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::put('login_locked_at', time());
        }
    }

    private static function clearFailures(): void
    {
        Session::forget('login_attempts');
        Session::forget('login_locked_at');
    }
}
