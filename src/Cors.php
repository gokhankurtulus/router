<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 07:19
 */


namespace Router;

class Cors
{
    protected static array $origins = ['*'];
    protected static array $methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    protected static array $headers = ['Authorization', 'Content-Type', 'Accept'];
    protected static array $exposedHeaders = [];

    protected static int $maxAge = 60;
    protected static bool $credentials = false;

    protected static string $contentType = "text/html;charset=utf-8";
    protected static string $contentSecurityPolicy = "default-src 'self'; script-src 'self'; style-src 'self'; img-src https://*";
    protected static string $xFrameOptions = "sameorigin";

    public static function getOrigins(): array
    {
        return static::$origins;
    }

    public static function setOrigins(array $origins): void
    {
        static::$origins = $origins;
    }

    public static function addToOrigins(string $origin): void
    {
        if (!in_array($origin, static::getOrigins())) {
            static::$origins[] = $origin;
        }
    }

    public static function getMethods(): array
    {
        return static::$methods;
    }

    public static function setMethods(array $methods): void
    {
        static::$methods = $methods;
    }

    public static function addToMethods(string $method): void
    {
        if (!in_array($method, static::getMethods())) {
            static::$methods[] = $method;
        }
    }

    public static function getHeaders(): array
    {
        return static::$headers;
    }

    public static function setHeaders(array $headers): void
    {
        static::$headers = $headers;
    }

    public static function addToHeaders(string $header): void
    {
        if (!in_array($header, static::getHeaders())) {
            static::$headers[] = $header;
        }
    }

    public static function getExposedHeaders(): array
    {
        return static::$exposedHeaders;
    }

    public static function setExposedHeaders(array $exposedHeaders): void
    {
        static::$exposedHeaders = $exposedHeaders;
    }

    public static function addToExposedHeaders(string $exposedHeader): void
    {
        if (!in_array($exposedHeader, static::getExposedHeaders())) {
            static::$exposedHeaders[] = $exposedHeader;
        }
    }

    public static function getMaxAge(): int
    {
        return static::$maxAge;
    }

    public static function setMaxAge(int $maxAge): void
    {
        static::$maxAge = max($maxAge, 0);
    }

    public static function getCredentials(): bool
    {
        return static::$credentials;
    }

    public static function setCredentials(bool $credentials): void
    {
        static::$credentials = $credentials;
    }

    public static function getContentType(): string
    {
        return static::$contentType;
    }

    public static function setContentType(string $contentType): void
    {
        static::$contentType = $contentType;
    }

    public static function getContentSecurityPolicy(): string
    {
        return static::$contentSecurityPolicy;
    }

    public static function setContentSecurityPolicy(string $contentSecurityPolicy): void
    {
        static::$contentSecurityPolicy = $contentSecurityPolicy;
    }

    public static function getXFrameOptions(): string
    {
        return static::$xFrameOptions;
    }

    public static function setXFrameOptions(string $xFrameOptions): void
    {
        static::$xFrameOptions = $xFrameOptions;
    }


    public static function handleCors(): void
    {
        if (Request::method() === 'OPTIONS') {
            header("Access-Control-Allow-Origin: " . implode(', ', array_map('trim', static::getOrigins())));
            header("Access-Control-Allow-Methods: " . implode(', ', array_map('trim', static::getMethods())));
            header("Access-Control-Allow-Headers: " . implode(', ', array_map('trim', static::getHeaders())));
            header("Access-Control-Expose-Headers: " . implode(', ', array_map('trim', static::getExposedHeaders())));
            header("Access-Control-Max-Age: " . static::getMaxAge());
            header("Access-Control-Allow-Credentials: " . (static::$credentials ? "true" : "false"));
        }
        header("Content-Type: " . static::getContentType());
        header("Content-Security-Policy: " . static::getContentSecurityPolicy());
        header("X-Frame-Options: " . static::getXFrameOptions());
    }
}