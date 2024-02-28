<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 28.02.2024 Time: 04:10
 */


namespace Router\Loggers;

use Logger\Logger;

class RouterLogger extends Logger
{
    protected static string $folderPath = "";
    protected static string $fileName = "router.log";
}