<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 28.02.2024 Time: 03:56
 */


namespace Router\Loggers;

use Logger\Logger;

class ViewLogger extends Logger
{
    protected static string $folderPath = "";
    protected static string $fileName = "views.log";
}