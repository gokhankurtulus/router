<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 10:46
 */


namespace Router;

use Router\Enums\HttpStatus;
use Router\Exceptions\HttpException;
use Router\Loggers\RouterLogger;

class Application
{
    protected static ?Request $request = null;
    protected static ?Response $response = null;
    protected static ?Router $router = null;

    public function __construct(callable $callback)
    {
        $this->handler($callback);
    }

    public function handler(callable $callback): void
    {
        try {
            ob_start();
            static::$request = new Request();
            static::$response = new Response();
            static::$router = new Router(static::$request, static::$response);

            call_user_func($callback);

            echo static::$router->resolve()->send();
            ob_end_flush();
        } catch (HttpException $httpException) {
            $httpStatus = HttpStatus::from($httpException->getCode());
            $message = $httpException->getMessage();;
            $this->sendErrorResponse($httpStatus, $message);
        } catch (\Exception|\Throwable $exception) {
            $message = "Message: {$exception->getMessage()}" . PHP_EOL;
            $file = "File: {$exception->getFile()}" . PHP_EOL;
            $line = "Line: {$exception->getLine()}" . PHP_EOL;
            $trace = "Trace:" . PHP_EOL . "{$exception->getTraceAsString()}" . PHP_EOL;

            $log = $message . $file . $line . $trace;
            RouterLogger::log($log);
            $this->sendErrorResponse(HttpStatus::INTERNAL_SERVER_ERROR, HttpStatus::INTERNAL_SERVER_ERROR->getMessage());
        }
    }

    protected function sendErrorResponse(HttpStatus $httpStatus, string $message): void
    {
        $error = [
            'code' => $httpStatus->value,
            'message' => $message
        ];
        if (static::$request::contentType() === "application/json" && static::$request::isAccepts('application/json')) {
            echo static::$response->status($httpStatus)
                ->json($error)
                ->send();
            return;
        }
        echo static::$response->status($httpStatus)
            ->view("_error", ['error' => $error], Resource::getErrorLayout())
            ->send();
    }
}