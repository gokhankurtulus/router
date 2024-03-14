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

    public function __destruct()
    {
        Response::handleHeaders();
    }

    /**
     * @return bool
     * @throws HttpException
     */
    public function resolve(): mixed
    {
        $requestPath = static::$request::path();
        $requestMethod = static::$request::method();
        $route = $this->checkRoute($requestPath, $requestMethod);

        return $this->handleEndpoint($route, $requestPath);
    }

    /**
     * @throws HttpException
     */
    protected function handleEndpoint(array $route, string $path): mixed
    {
        if (!$route['endpoint_exists'])
            throw new HttpException(HttpStatus::NOT_FOUND);

        if (!$route['method_exists'])
            throw new HttpException(HttpStatus::METHOD_NOT_ALLOWED);

        if (!$route['endpoint'])
            throw new HttpException(HttpStatus::NOT_FOUND);

        $endpoint = $route['endpoint'];

        $matches = $this->matchRoute($endpoint, $path);
        if ($matches !== false) {
            if (is_callable($endpoint['action'])) {
                return call_user_func($endpoint['action'], $matches);
            } elseif (isset($endpoint['controller']) && class_exists($endpoint['controller'])) {
                $controller = new $endpoint['controller']($matches, static::$request, static::$response);
                if ($controller && is_callable([$controller, $endpoint['action']])) {
                    return call_user_func([$controller, $endpoint['action']]);
                }
            }
        }

        return false;
    }

    protected function checkRoute(string $path, string $method): array
    {
        $route = $this->findMatchingRoute($path, $method);
        if ($route['endpoint_exists'] && !$route['method_exists']) {
            $route = $this->findMatchingRoute($path, 'ANY');
        }
        return $route;
    }

    protected function findMatchingRoute(string $path, string $method): ?array
    {
        $routeData = [
            'endpoint_exists' => false,
            'method_exists' => false,
            'endpoint' => null,
        ];
        foreach (static::getRoutes() as $route) {
            if ($this->matchRoute($route, $path) !== false) {
                $routeData['endpoint_exists'] = true;
                if ($route['method'] === $method) {
                    $routeData['method_exists'] = true;
                    $routeData['endpoint'] = $route;
                    break;
                }
            }
        }
        return $routeData;
    }

    protected function matchRoute(array &$route, string $path): false|array
    {
        $route['path'] = preg_replace('/{([^}]+)}/', '(?<\1>[^/]+)', $route['path']);
        if (preg_match("#^{$route['path']}/?$#", $path, $matches))
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        return false;
    }

    public static function getRoutes(): array
    {
        return static::$routes;
    }

    protected static function setRoutes(array $routes): void
    {
        static::$routes = $routes;
    }

    public static function addRoute(array $route): void
    {
        static::$routes[] = $route;
    }
}