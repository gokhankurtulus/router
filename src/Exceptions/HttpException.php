<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 06:30
 */


namespace Router\Exceptions;

use Router\Enums\HttpStatus;

class HttpException extends \Exception
{
    public function __construct(HttpStatus $httpStatus, string $message = "")
    {
        if (empty($message))
            $this->message = $httpStatus->getMessage();
        $this->code = $httpStatus->value;
    }
}