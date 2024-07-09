<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:41
 */


namespace Router;

use Router\Abstracts\Middleware;
use Router\Enums\HttpStatus;
use Router\Exceptions\HttpException;
use Router\Exceptions\RouterException;

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
     * @return Response
     * @throws HttpException|RouterException
     */
    public function resolve(): Response
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
     * @return Response
     * @throws HttpException|RouterException
     */
    protected function handleMatchedRoute(array $matchedRoute): Response
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
     * @return Response
     * @throws RouterException
     */
    protected function executeRouteAction(array $route, array $matches): Response
    {
        $request = static::$request;
        $response = static::$response;

        if (!empty($route['middlewares']) && is_array($route['middlewares'])) {
            foreach ($route['middlewares'] as $middlewareClass) {
                if (!class_exists($middlewareClass)) {
                    throw new RouterException("Middleware class {$middlewareClass} does not exist.");
                }

                // Check if the middleware class is a subclass of the Middleware abstract class
                if (!is_subclass_of($middlewareClass, Middleware::class)) {
                    throw new RouterException("Class {$middlewareClass} is not a middleware.");
                }
            }

            $middlewareChain = array_reduce(
            // Reverse the middleware array. Because the last middleware in the array should be the first one to process the request.
                array_reverse($route['middlewares']),
                // For each middleware, create a new instance and call its handle method. Process the request through each middleware.
                fn($next, $middlewareClass) => fn($request) => (new $middlewareClass)->handle($request, $response, $next),
                // Start with a closure that calls the route action. This is the final action to be performed after all middlewares have processed the request.
                fn($request) => $this->callAction($route, $matches)
            );

            return $middlewareChain($request);
        }

        return $this->callAction($route, $matches);
    }

    /**
     * Call the action of the matched route. The action can be a callable or a controller method.
     * @param array $route
     * @param array $matches
     * @return Response
     * @throws RouterException
     */
    protected function callAction(array $route, array $matches): Response
    {
        $result = null;

        if (is_callable($route['action'])) {
            $result = call_user_func($route['action'], $matches, static::$request, static::$response);
        } elseif (
            isset($route['controller'])
            && class_exists($route['controller'])
            && is_subclass_of($route['controller'], Abstracts\Controller::class)
        ) {
            $controller = new $route['controller']($matches, static::$request, static::$response);

            if (is_callable([$controller, $route['action']])) {
                $result = call_user_func([$controller, $route['action']]);
            } else {
                throw new RouterException("Action {$route['action']} does not exist in controller {$route['controller']} or is not callable.");
            }
        } else {
            throw new RouterException("Action {$route['action']} is not callable.");
        }

        if (!$result instanceof \Router\Response) {
            $responseClass = \Router\Response::class;
            throw new RouterException("Action {$route['action']} must return an instance of {$responseClass}.");
        }
        return $result;
    }

    /**
     * Find a matching route based on the request path and method.
     *
     * @param string $path
     * @param string $method
     * @return array Information about the matched route.
     */
    protected function findMatchingRoute(string $path, string $method): array
    {
        // Iterate over all registered routes.
        foreach (static::getRoutes() as $routePath => $routes) {
            // If the request path does not match the pattern, continue with the next route.
            if (!$this->matchPathToRoute(['path' => $routePath], $path)) {
                continue;
            }
            // If the request path matches the pattern, check if the request method matches any of the route's methods.
            foreach ($routes as $route) {
                // If the request method matches the route's method, return information about the route.
                if ($route['method'] === $method || $route['method'] === 'ANY') {
                    return [
                        'exists' => true,
                        'method_matches' => true,
                        'route' => $route
                    ];
                }
            }
            // If the request method does not match any of the route's methods, return information about the first route.
            return [
                'exists' => true,
                'method_matches' => false,
                'route' => $routes[0]
            ];
        }
        // If no route was matched, return null.
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
     * @return bool True if the request path matches the route path pattern, false otherwise.
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
     * @return array An associative array of route parameters.
     */
    protected function extractRouteMatches(string $routePath, string $requestPath): array
    {
        $pattern = $this->createPatternFromPath($routePath);
        if (preg_match("#^{$pattern}/?$#", $requestPath, $matches)) {
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        }
        return [];
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
        static::$routes[$route['path']][] = $route;
    }
}