<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 24.03.2024 Time: 04:56
 */


namespace Router;

abstract class Middleware
{
    abstract public function handle(Request $request, \Closure $next): mixed;
}