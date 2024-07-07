<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 24.03.2024 Time: 04:56
 */


namespace Router\Abstracts;

use Router\Request;
use Router\Response;

abstract class Middleware
{
    abstract public function handle(Request $request, Response $response, \Closure $next): mixed;
}