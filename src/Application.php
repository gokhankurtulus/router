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
            static::$request = new Request();
            static::$response = new Response();
            static::$router = new Router(static::$request, static::$response);

            call_user_func($callback);
            echo static::$router->resolve();
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
        if (static::$request::contentType() === "text/html" || static::$request::isAccepts('text/html')) {
            $error = [
                'code' => $httpStatus->value,
                'message' => $message
            ];
            echo View::render("_error", ['error' => $error], View::getErrorLayout());
        }
        static::$response->setHttpStatus($httpStatus)
            ->setSuccess(false)
            ->setCache(false)
            ->addMessage($message)
            ->send();
    }
}