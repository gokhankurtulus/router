<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 6.07.2024 Time: 15:53
 */

namespace Router\Traits\Resource;

trait Source
{
    private static string $sourceUri = "";

    public static function getSourceUri(): string
    {
        return static::$sourceUri;
    }

    public static function setSourceUri(string $sourcePath): void
    {
        static::$sourceUri = $sourcePath;
    }

    public static function css(string $file): string
    {
        return static::getSourceUri() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . htmlspecialchars($file);
    }

    public static function js(string $file): string
    {
        return static::getSourceUri() . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . htmlspecialchars($file);
    }

    public static function img(string $file): string
    {
        return static::getSourceUri() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . htmlspecialchars($file);
    }
}