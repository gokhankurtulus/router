<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.03.2024 Time: 03:08
 */

namespace Router\Traits;

use Router\Loggers\ViewLogger;

trait Views
{
    protected static string $viewsPath = "";
    protected static string $errorLayout = "";
    protected static array $carryData = [];
    protected static array $fileExistenceCache = [];

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

    public static function getCarryData(): array
    {
        return static::$carryData;
    }

    public static function carry(array $data = []): void
    {
        static::$carryData = array_merge(static::$carryData, $data);
    }

    protected function render(string $view, array $params = [], ?string $layout = null): false|string
    {
        $params = array_merge(static::$carryData, $params);
        $viewContent = $this->renderContent($view, $params);
        if ($layout) {
            $viewLayout = $this->renderLayout($layout, $params);
            if ($viewLayout) {
                return str_replace('{{content}}', $viewContent, $viewLayout);
            }
        }
        return $viewContent;
    }

    protected static function renderLayout(string $layout, array $params = []): false|string
    {
        $path = static::getViewsPath() . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . "$layout.phtml";
        return static::renderFile($path, $params, "renderLayout");
    }

    protected static function renderContent(string $view, array $params = []): false|string
    {
        $path = static::getViewsPath() . DIRECTORY_SEPARATOR . "$view.phtml";
        return static::renderFile($path, $params, "renderContent");
    }

    protected static function checkFileExistence(string $path): bool
    {
        if (!isset(static::$fileExistenceCache[$path])) {
            static::$fileExistenceCache[$path] = is_file($path) && is_dir(dirname($path));
        }
        return static::$fileExistenceCache[$path];
    }

    protected static function renderFile(string $path, array $params, string $methodName): false|string
    {
        if (!static::checkFileExistence($path)) {
            ViewLogger::log("\"$path\" is not an existing file or its directory does not exist.", $methodName);
            return false;
        }
        extract($params, EXTR_SKIP);
        ob_start();
        include_once $path;
        return ob_get_clean();
    }
}