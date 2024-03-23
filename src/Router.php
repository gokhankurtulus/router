<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:41
 */


namespace Router;

use Router\Enums\HttpStatus;
use Router\Exceptions\HttpException;

class Router
{
    protected static ?Request $request = null;
    protected static ?Response $response = null;
    protected static array $routes = [];

    public function __construct(?Request $request = null, ?Response $response = null)
    {
        static::$request = $request ?? new Request();
        static::$response = $response ?? new Response();
    }

    /**
     * Resolve the current request and find the route to handle it.
     *
     * @return mixed
     * @throws HttpException
     */
    public function resolve(): mixed
    {
        $matchedRoute = $this->findMatchingRoute(
            static::$request::path(),
            static::$request::method()
        );

        return $this->handleMatchedRoute($matchedRoute);
    }

    /**
     * Handle the matched route by validating and executing its action.
     *
     * @param array $matchedRoute
     * @return mixed
     * @throws HttpException
     */
    protected function handleMatchedRoute(array $matchedRoute): mixed
    {
        $this->validateMatchedRoute($matchedRoute);

        $matches = $this->extractRouteMatches(
            $matchedRoute['route']['path'],
            static::$request::path()
        );

        return $this->executeRouteAction($matchedRoute['route'], $matches);
    }

    /**
     * Validate if the matched route exists and if the HTTP method is allowed.
     *
     * @param array $matchedRoute
     * @throws HttpException
     */
    protected function validateMatchedRoute(array $matchedRoute): void
    {
        if (!$matchedRoute['exists']) {
            throw new HttpException(HttpStatus::NOT_FOUND);
        }

        if (!$matchedRoute['method_matches']) {
            throw new HttpException(HttpStatus::METHOD_NOT_ALLOWED);
        }
    }

    /**
     * Execute the route action based on the matched route.
     *
     * @param array $route
     * @param array $matches
     * @return mixed
     */
    protected function executeRouteAction(array $route, array $matches): mixed
    {
        if (is_callable($route['action'])) {
            return call_user_func($route['action'], $matches);
        }

        if (isset($route['controller']) && class_exists($route['controller'])) {
            $controller = new $route['controller']($matches, static::$request, static::$response);

            if (is_callable([$controller, $route['action']])) {
                return call_user_func([$controller, $route['action']]);
            }
        }

        return false;
    }

    /**
     * Find a matching route based on the request path and method.
     *
     * @param string $path
     * @param string $method
     * @return array|null
     */
    protected function findMatchingRoute(string $path, string $method): ?array
    {
        foreach (static::getRoutes() as $route) {
            if ($this->matchPathToRoute($route, $path)) {
                if ($route['method'] === $method || $route['method'] === 'ANY') {
                    return [
                        'exists' => true,
                        'method_matches' => true,
                        'route' => $route
                    ];
                }

                return [
                    'exists' => true,
                    'method_matches' => false,
                    'route' => $route
                ];
            }
        }

        return [
            'exists' => false,
            'method_matches' => false,
            'route' => null
        ];
    }

    /**
     * Create a regular expression pattern from the given path.
     *
     * This method replaces route parameters in the path with named capturing groups.
     *
     * @param string $path The path containing route parameters.
     * @return string The regular expression pattern.
     */
    protected function createPatternFromPath(string $path): string
    {
        return preg_replace('/{([^}]+)}/', '(?<\1>[^/]+)', $path);
    }

    /**
     * Check if the request path matches the route path pattern.
     *
     * @param array $route
     * @param string $path
     * @return bool
     */
    protected function matchPathToRoute(array $route, string $path): bool
    {
        $pattern = $this->createPatternFromPath($route['path']);
        return (bool)preg_match("#^{$pattern}/?$#", $path);
    }

    /**
     * Extract the route parameters from the request path.
     *
     * This method matches the request path against the route's pattern and extracts
     * any named capturing groups as route parameters.
     *
     * @param string $routePath
     * @param string $requestPath
     * @return array|false
     */
    protected function extractRouteMatches(string $routePath, string $requestPath): array|false
    {
        $pattern = $this->createPatternFromPath($routePath);
        if (preg_match("#^{$pattern}/?$#", $requestPath, $matches)) {
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        }
        return false;
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
     * Set the routes for the router.
     *
     * @param array $routes
     */
    public static function setRoutes(array $routes): void
    {
        static::$routes = $routes;
    }

    /**
     * Add a new route to the router.
     *
     * @param array $route
     */
    public static function addRoute(array $route): void
    {
        static::$routes[] = $route;
    }
}