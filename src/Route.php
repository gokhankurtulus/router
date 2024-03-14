<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:06
 */


namespace Router;

class Route
{
    protected static ?string $method = null;
    protected static ?string $path = null;
    protected static ?string $prefix = null;
    protected static ?string $controller = null;
    protected static mixed $action = null;

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

    public static function group(callable $callback): void
    {
        $callback();
    }

    public static function get(string $path, mixed $callback): void
    {
        static::$method = 'GET';
        static::$path = static::$prefix ? static::$prefix . $path : $path;
        static::addRoute($callback);
    }

    public static function post(string $path, mixed $callback): void
    {
        static::$method = 'POST';
        static::$path = static::$prefix ? static::$prefix . $path : $path;
        static::addRoute($callback);
    }

    public static function put(string $path, mixed $callback): void
    {
        static::$method = 'PUT';
        static::$path = static::$prefix ? static::$prefix . $path : $path;
        static::addRoute($callback);
    }

    public static function any(string $path, mixed $callback): void
    {
        static::$method = 'ANY';
        static::$path = static::$prefix ? static::$prefix . $path : $path;
        static::addRoute($callback);
    }

    protected static function addRoute(mixed $callback): void
    {
        $method = static::$method;
        $path = static::$path;
        $controller = static::$controller;
        $action = static::$action;

        if (is_array($callback)) {
            [$controller, $action] = $callback;
        } else {
            $action = $callback;
        }

        $route = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
        Router::addRoute($route);
    }
}