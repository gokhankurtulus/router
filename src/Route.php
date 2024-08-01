<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:06
 */


namespace Router;

use Router\Loggers\RouterLogger;

class Route
{
    protected static array $routes = [];
    protected static ?string $name = null;
    protected static ?string $prefix = null;
    protected static ?string $controller = null;
    protected static array $middlewares = [];

    /**
     * Set the name for the route.
     *
     * @param string $name
     * @return static
     */
    public static function name(string $name): static
    {
        static::$name = $name;
        return new static;
    }

    /**
     * Set the prefix for the route group.
     *
     * @param string $prefix
     * @param callable $callback
     * @return static
     */
    public static function prefix(string $prefix, callable $callback): static
    {
        static::$prefix = $prefix;
        $callback();
        static::$prefix = null;
        return new static;
    }

    /**
     * Set the controller for the route group.
     *
     * @param string $controller
     * @param callable $callback
     * @return static
     */
    public static function controller(string $controller, callable $callback): static
    {
        static::$controller = $controller;
        $callback();
        static::$controller = null;
        return new static;
    }

    /**
     * Set the middleware for the route group.
     *
     * @param array|string $middlewares
     * @param callable $callback
     * @return static
     */
    public static function middleware(array|string $middlewares, callable $callback): static
    {
        static::$middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
        $callback();
        static::$middlewares = [];
        return new static;
    }

    /**
     * Define a group of routes.
     *
     * @param callable $callback
     * @return void
     */
    public static function group(callable $callback): void
    {
        $callback();
    }

    /**
     * Register a GET route.
     *
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function get(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('GET', $path, $callback, $middlewares);
    }

    /**
     * Register a POST route.
     *
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function post(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('POST', $path, $callback, $middlewares);
    }

    /**
     * Register a PUT route.
     *
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function put(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('PUT', $path, $callback, $middlewares);
    }

    /**
     * Register a PATCH route.
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function patch(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('PATCH', $path, $callback, $middlewares);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function delete(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('DELETE', $path, $callback, $middlewares);
    }

    /**
     * Register a route that responds to any HTTP method.
     *
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function any(string $path, mixed $callback, array $middlewares = []): void
    {
        static::addRoute('ANY', $path, $callback, $middlewares);
    }

    /**
     * Get all registered routes.
     *
     * @return array
     */
    public static function getRoutes(): array
    {
        return static::$routes;
    }

    /**
     * Add a route to the routes array.
     *
     * @param string $method
     * @param string $path
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
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
            'name' => static::$name,
            'method' => $method,
            'path' => static::$prefix ? static::$prefix . $path : $path,
            'controller' => $controller,
            'middlewares' => $middlewares,
            'action' => $action,
        ];
        // Access routes by path for faster matching.
        static::$routes[$route['path']][] = $route;

        static::$name = null;
    }

    /**
     * Generate a URL to a named route.
     *
     * @param string $name The name of the route.
     * @param array|null $params Optional parameters to replace in the route path.
     * @return string
     */
    public static function toRoute(string $name, ?array $params = null): string
    {
        foreach (static::$routes as $path => $routes) {
            foreach ($routes as $route) {
                if ($route['name'] === $name) {
                    $path = $route['path'];
                    if ($params) {
                        foreach ($params as $key => $value) {
                            $path = str_replace('{' . $key . '}', $value, $path);
                        }
                    }
                    return Request::host() . $path;
                }
            }
        }
        RouterLogger::log("Route '{$name}' not defined.");
        return '';
    }
}
