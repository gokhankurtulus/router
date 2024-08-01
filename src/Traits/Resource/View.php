<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 6.07.2024 Time: 15:47
 */

namespace Router\Traits\Resource;

use Router\Loggers\ViewLogger;

trait View
{
    protected static string $viewsPath = "";
    protected static string $errorLayout = "";
    protected static array $pageTags = [];
    protected static array $carryData = [];
    protected static array $fileExistenceCache = [];

    /**
     * Get views path.
     *
     * @return string
     */
    public static function getViewsPath(): string
    {
        return static::$viewsPath;
    }

    /**
     * Set views path.
     *
     * @param string $viewsPath
     * @return void
     */
    public static function setViewsPath(string $viewsPath): void
    {
        static::$viewsPath = realpath($viewsPath);
    }

    /**
     * Check if a view file exists.
     *
     * @param string $view
     * @return bool
     */
    public static function hasView(string $view): bool
    {
        return file_exists(static::getViewsPath() . DIRECTORY_SEPARATOR . "$view.phtml");
    }

    /**
     * Check if a layout file exists.
     *
     * @param string $layout
     * @return bool
     */
    public static function hasLayout(string $layout): bool
    {
        return file_exists(static::getViewsPath() . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . "$layout.phtml");
    }

    /**
     * Get the error layout.
     *
     * @return string
     */
    public static function getErrorLayout(): string
    {
        return static::$errorLayout;
    }

    /**
     * Set the error layout.
     *
     * @param string $errorLayout
     * @return void
     */
    public static function setErrorLayout(string $errorLayout): void
    {
        static::$errorLayout = $errorLayout;
    }

    /**
     * Get the page tags.
     *
     * @return array
     */
    public static function getPageTags(): array
    {
        return static::$pageTags;
    }

    /**
     * Set the page tags.
     *
     * @param array $data
     * @return void
     */
    public static function setPageTags(array $data): void
    {
        static::$pageTags = array_unique(array_merge(static::$pageTags, $data));
    }

    /**
     * Get the carry data.
     *
     * @return array
     */
    public static function getCarryData(): array
    {
        return static::$carryData;
    }

    /**
     * Carry data to be used in views.
     *
     * @param array $data
     * @return void
     */
    public static function carry(array $data = []): void
    {
        static::$carryData = array_unique(array_merge(static::$carryData, $data));
    }

    /**
     * Render a view with optional layout.
     *
     * @param string $view
     * @param array $params
     * @param string|null $layout
     * @return false|string
     */
    public static function render(string $view, array $params = [], ?string $layout = null): false|string
    {
        $params = array_merge(static::$carryData, $params);
        $viewContent = static::renderContent($view, $params);
        $content = $viewContent;

        if ($layout) {
            $viewLayout = static::renderLayout($layout, $params);
            if ($viewLayout) {
                $content = str_replace('{{content}}', $viewContent, $viewLayout);
            }
        }

        $tags = static::getPageTags();
        if (!empty($tags)) {
            foreach ($tags as $key => $value) {
                $content = static::str_replace_first("{{{$key}}}", $value, $content);
            }
        }

        return $content;
    }

    /**
     * Render a layout file.
     *
     * @param string $layout
     * @param array $params
     * @return false|string
     */
    protected static function renderLayout(string $layout, array $params = []): false|string
    {
        $path = static::getViewsPath() . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . "$layout.phtml";
        return static::renderFile($path, $params, "renderLayout");
    }

    /**
     * Render a view file.
     *
     * @param string $view
     * @param array $params
     * @return false|string
     */
    protected static function renderContent(string $view, array $params = []): false|string
    {
        $path = static::getViewsPath() . DIRECTORY_SEPARATOR . "$view.phtml";
        return static::renderFile($path, $params, "renderContent");
    }

    /**
     * Render a file with given parameters.
     *
     * @param string $path
     * @param array $params
     * @param string $methodName
     * @return false|string
     */
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

    /**
     * Check if a file exists and its directory is valid.
     *
     * @param string $path
     * @return bool
     */
    protected static function checkFileExistence(string $path): bool
    {
        if (!isset(static::$fileExistenceCache[$path])) {
            static::$fileExistenceCache[$path] = is_file($path) && is_dir(dirname($path));
        }
        return static::$fileExistenceCache[$path];
    }

    /**
     * Replace the first occurrence of a string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    protected static function str_replace_first(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        return $pos === false ? $subject : substr_replace($subject, $replace, $pos, strlen($search));
    }
}
