<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:04
 */


namespace Router;

class Request
{
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? "";
    }

    public static function host(): string
    {
        $protocol = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = 'https';
        }

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    public static function uri(): string
    {
        return static::host() . $_SERVER["REQUEST_URI"] ?? "";
    }

    public static function path(): string
    {
        return (string)parse_url($_SERVER['REQUEST_URI'] ?? "", PHP_URL_PATH);
    }

    public static function parsedPath(): array
    {
        $path = ltrim(static::path(), '/');
        return explode('/', $path);
    }

    public static function queryParameters(): array
    {
        $query = (string)parse_url($_SERVER['REQUEST_URI'] ?? "", PHP_URL_QUERY);

        parse_str($query, $params);
        return array_map('htmlspecialchars', $params);
    }

    public static function headers(): bool|array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = array();

        $copy_server = array(
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5' => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerKey] = $value;
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = $_SERVER['PHP_AUTH_PW'] ?? '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }

    public static function accept(): array
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        return array_map('trim', explode(',', $acceptHeader));
    }

    public static function isAccept(string $type): bool
    {
        return in_array($type, static::accept());
    }

    public static function acceptLanguage(): array
    {
        $acceptLanguageHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        return array_map(fn($language) => explode(';', trim($language))[0], explode(',', $acceptLanguageHeader));
    }

    public static function isAcceptLanguage(string $language): bool
    {
        return in_array($language, static::acceptLanguage());
    }

    public static function acceptLanguagePriority(): array
    {
        $acceptLanguageHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $languages = array_map('trim', explode(',', $acceptLanguageHeader));
        $languages = array_map(function ($language) {
            $parts = explode(';', $language);
            $priority = 1.0;
            if (count($parts) > 1) {
                $priority = (float)explode('=', $parts[1])[1];
            }
            return ['language' => $parts[0], 'priority' => $priority];
        }, $languages);
        usort($languages, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
        return array_map(fn($language) => $language['language'], $languages);
    }

    public static function contentType(): string
    {
        return $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? "";
    }

    public static function ip(): string
    {
        $envList = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($envList as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return (string)$ip;
                    }
                }
            }
        }
        return 'Unknown'; // Default value when no valid IP is found
    }

    public static function agent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function fields(?string $input = null): array
    {
        $fields = [];
        $method = static::method();
        $contentType = static::contentType();

        if ($method === 'GET') {
            $fields = static::queryParameters();
        } elseif (str_starts_with($contentType, 'multipart/form-data')) {
            $fields = array_map('htmlspecialchars', $_POST);
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            $fields = array_map('htmlspecialchars', $_POST);
        } elseif ($contentType === 'application/json') {
            $json = $input ?? file_get_contents('php://input');
            $fields = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
            $fields = array_map('htmlspecialchars', $fields);
        } elseif ($contentType === 'application/xml' || $contentType === 'text/xml') {
            $xml = simplexml_load_string($input ?? file_get_contents('php://input'), 'SimpleXMLElement', LIBXML_NOCDATA);
            $fields = json_decode(json_encode((array)$xml), true);
            $fields = array_map('htmlspecialchars', $fields);
        }

        return $fields;
    }

    public static function files(): array
    {
        $files = [];
        foreach ($_FILES ?? [] as $inputName => $fileInfo) {
            if (!is_array($fileInfo['name'])) {
                $files[] = ['input_name' => $inputName, ...$fileInfo];
            } else {
                foreach (array_keys($fileInfo['name']) as $key) {
                    $files[] = [
                        'input_name' => $inputName,
                        'name' => $fileInfo['name'][$key],
                        'full_path' => $fileInfo['full_path'][$key],
                        'type' => $fileInfo['type'][$key],
                        'tmp_name' => $fileInfo['tmp_name'][$key],
                        'error' => $fileInfo['error'][$key],
                        'size' => $fileInfo['size'][$key],
                    ];
                }
            }
        }

        return $files;
    }
}
