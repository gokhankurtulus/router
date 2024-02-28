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

    public static function headers(): bool|array
    {
        return getallheaders();
    }

    public static function accepts(): array
    {
        return explode(',', static::headers()['Accept'] ?? '');
    }

    public static function isAccepts(string $type): bool
    {
        return in_array($type, static::accepts());
    }

    public static function contentType(): string
    {
        return $_SERVER['CONTENT_TYPE'] ?? "";
    }

    public static function host(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') !== 0 ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    public static function uri(): string
    {
        return static::host() . $_SERVER["REQUEST_URI"] ?? "";
    }

    public static function path(): string
    {
        $urlParsed = parse_url($_SERVER['REQUEST_URI']);
        return $urlParsed['path'];
    }

    public static function parsedPath(): array
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = ltrim($path, '/');
        return explode('/', $path);
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
        return (string)$_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function queryParameters(): array
    {
        $parameters = [];
        foreach ($_GET ?? [] as $key => $value) {
            $parameters[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_URL);
        }
        return $parameters;
    }

    public static function fields(): mixed
    {
        $fields = [];
        $method = static::method();
        $contentType = static::contentType();

        if ($method === 'GET') {
            $fields = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
        } elseif (str_starts_with($contentType, 'multipart/form-data')) {
            $fields = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            $fields = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        } elseif ($contentType === 'application/json') {
            $fields = json_decode(file_get_contents('php://input'), true);
        } elseif ($contentType === 'application/xml' || $contentType === 'text/xml') {
            $xml = simplexml_load_string(file_get_contents('php://input'), 'SimpleXMLElement', LIBXML_NOCDATA);
            $fields = json_decode(json_encode((array)$xml), true);
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