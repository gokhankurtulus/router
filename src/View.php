<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 09:53
 */


namespace Router;

use Router\Loggers\ViewLogger;

class View
{
    protected static string $viewsPath = "";
    protected static string $errorLayout = "";
    protected static array $carryData = [];

    public static function getViewsPath(): string
    {
        return static::$viewsPath;
    }

    public static function setViewsPath(string $viewsPath): void
    {
        static::$viewsPath = $viewsPath;
    }

    public static function getErrorLayout(): string
    {
        return static::$errorLayout;
    }

    public static function setErrorLayout(string $errorLayout): void
    {
        static::$errorLayout = $errorLayout;
    }

    public static function carry(array $data = []): void
    {
        static::$carryData = array_merge(static::$carryData, $data);
    }

    public static function render(string $view, array $params = [], ?string $layout = null): false|string
    {
        $params = array_merge(static::$carryData, $params);
        $viewContent = static::renderContent($view, $params);
        if ($layout) {
            $viewLayout = static::renderLayout($layout, $params);
            if ($viewLayout) {
                return str_replace('{{content}}', $viewContent, $viewLayout);
            }
        }
        return $viewContent;
    }

    protected static function renderLayout(string $layout, array $params = []): false|string
    {
        $dirPath = static::getViewsPath();
        $layoutsPath = $dirPath . DIRECTORY_SEPARATOR . 'layouts';
        $path = $layoutsPath . DIRECTORY_SEPARATOR . "$layout.phtml";
        if (!is_dir($dirPath)) {
            ViewLogger::log("\"$dirPath\" is not an existing directory.", "renderLayout");
        }
        if (!is_dir($layoutsPath)) {
            ViewLogger::log("\"$dirPath\" is not an existing layout directory.", "renderLayout");
        }
        if (!is_file($path)) {
            ViewLogger::log("\"$path\" is not an existing file.", "renderLayout");
        }
        if (is_file($path)) {
            extract($params, EXTR_SKIP);
            ob_start();
            include_once $path;
            return ob_get_clean();
        }
        return false;
    }

    protected static function renderContent(string $view, array $params = []): false|string
    {
        $dirPath = static::getViewsPath();
        $path = $dirPath . DIRECTORY_SEPARATOR . "$view.phtml";
        if (!is_dir($dirPath)) {
            ViewLogger::log("\"$dirPath\" is not an existing directory.", "renderContent");
        }
        if (!is_file($path)) {
            ViewLogger::log("\"$path\" is not an existing file.", "renderContent");
        }
        if (is_file($path)) {
            extract($params, EXTR_SKIP);
            ob_start();
            include_once $path;
            return ob_get_clean();
        }
        return false;
    }

}