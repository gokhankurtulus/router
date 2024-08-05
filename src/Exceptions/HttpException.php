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
        $this->code = $httpStatus->value;
        $this->message = $message ?: $httpStatus->getMessage();

        parent::__construct($this->message, $this->code);
    }
}