<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:06
 */


namespace Router;

class Route
{
    protected static ?string $prefix = null;
    protected static ?string $controller = null;
    protected static array $middlewares = [];

    public static function controller(string $controller, callable $callback): static
    {
        static::$controller = $controller;
        $callback();
        static::$controller = null;
        return new static;
    }

    public static function prefix(string $prefix, callable $callback): static
    {
        static::$prefix = $prefix;
        $callback();
        static::$prefix = null;
        return new static;
    }

    public static function middleware($middlewares, callable $callback): static
    {
        static::$middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
        $callback();
        static::$middlewares = [];
        return new static;
    }

    public static function group(callable $callback): void
    {
        $callback();
    }

    public static function get(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('GET', $path, $callback, $middlewares);
    }

    public static function post(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('POST', $path, $callback, $middlewares);
    }

    public static function put(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('PUT', $path, $callback, $middlewares);
    }

    public static function delete(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('DELETE', $path, $callback, $middlewares);
    }

    public static function any(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('ANY', $path, $callback, $middlewares);
    }

    protected static function addRoute(string $method, string $path, mixed $callback, array $middlewares = []): void
    {
        $controller = static::$controller;
        $action = null;

        if (is_array($callback)) {
            [$controller, $action] = $callback;
        } else {
            $action = $callback;
        }

        $middlewares = array_unique(array_merge(static::$middlewares, $middlewares));

        $route = [
            'method' => $method,
            'path' => static::$prefix ? static::$prefix . $path : $path,
            'controller' => $controller,
            'middlewares' => $middlewares,
            'action' => $action,
        ];
        Router::addRoute($route);
    }
}