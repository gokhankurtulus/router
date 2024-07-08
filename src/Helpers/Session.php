<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.07.2024 Time: 00:43
 */


namespace Router\Helpers;

class Session
{
    public static function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function setSessionCookieHttpOnly(bool $value): void
    {
        ini_set('session.cookie_httponly', $value ? '1' : '0');
    }

    public static function setSessionUseOnlyCookies(bool $value): void
    {
        ini_set('session.use_only_cookies', $value ? '1' : '0');
    }

    public static function setSessionCookieSecure(bool $value): void
    {
        ini_set('session.cookie_secure', $value ? '1' : '0');
    }

    public static function setSessionCookieSameSite(string $value): void
    {
        if (!in_array($value, ['None', 'Lax', 'Strict'])) {
            throw new \InvalidArgumentException('Invalid SameSite value. Valid values are "None", "Lax", or "Strict".');
        }
        ini_set('session.cookie_samesite', $value);
    }
}