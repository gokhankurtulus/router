<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 6.07.2024 Time: 15:47
 */

namespace Router\Traits\Resource;

use Router\Exceptions\ResourceException;

trait File
{
    private static string $uploadPath = "";

    public static function setUploadPath(string $path): void
    {
        static::$uploadPath = realpath($path);
    }

    public static function getUploadPath(): string
    {
        return static::$uploadPath;
    }

    public static function get(string $file, ?string $extraPath = null): array
    {
        $path = static::getUploadPath() . DIRECTORY_SEPARATOR . ($extraPath ?? '') . DIRECTORY_SEPARATOR . htmlspecialchars($file);
        if (!file_exists($path)) {
            throw new ResourceException("File not found");
        }
        return [
            'name' => htmlspecialchars($file),
            'path' => $path,
            'mime_type' => mime_content_type($path),
            'size' => filesize($path),
            'content' => file_get_contents($path),
        ];
    }

    public static function upload(array $file, bool $randomName = false, ?string $extraPath = null): string
    {
        $path = static::getUploadPath();
        if ($extraPath)
            $path .= DIRECTORY_SEPARATOR . $extraPath;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $fileName = $randomName ? static::generateRandomName($file['name']) : $file['name'];
        $path .= DIRECTORY_SEPARATOR . $fileName;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $fileName;
        }
        throw new ResourceException("File could not be uploaded");
    }

    protected static function generateRandomName(string $originalFileName): string
    {
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        return md5(uniqid(rand(), true)) . '.' . $extension;
    }
}