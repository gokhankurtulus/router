<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.07.2024 Time: 01:03
 */


namespace Router\Helpers;

class Cookie
{
    /**
     * Get a cookie.
     *
     * @param string $key The key of the cookie.
     * @return string|null The value of the cookie or null if the cookie does not exist.
     */
    public static function get(string $key): ?string
    {
        return $_COOKIE[$key] ?? null;
    }

    /**
     * Set a cookie.
     *
     * @param string $key The key of the cookie.
     * @param string $value The value to be stored in the cookie.
     * @param int $expiry The expiry time of the cookie (default is 3600 sec =  1 hour).
     * @param string $path The path on the server in which the cookie will be available on.
     * @param string $domain The domain that the cookie is available to.
     * @param bool $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection.
     * @param bool $httponly When TRUE the cookie will be made accessible only through the HTTP protocol.
     * @param string $samesite Allows you to declare if your cookie should be restricted to a first-party or same-site context.
     */
    public static function set(string $key, string $value, int $expiry = 3600, string $path = "/", string $domain = "", bool $secure = false, bool $httponly = true, string $samesite = 'Lax'): void
    {
        setcookie($key, $value, [
            'expires' => time() + $expiry,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    }

    /**
     * Check if a cookie exists.
     *
     * @param string $key The key of the cookie.
     * @return bool True if the cookie exists, false otherwise.
     */
    public static function exists(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Remove a cookie.
     *
     * @param string $key The key of the cookie.
     */
    public static function remove(string $key): void
    {
        setcookie($key, "", time() - 3600, "/");
    }
}