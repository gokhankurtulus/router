<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 01:05
 */


namespace Router;

use Router\Exceptions\HttpRequestException;

class HttpRequest
{
    protected string $method = "";
    protected string $uri = "";
    protected array $headers = [];
    protected mixed $body = null;
    protected array $options = [];

    public static function get(string $uri): static
    {
        return (new static())->method('GET')->uri($uri);
    }

    public static function post(string $uri): static
    {
        return (new static())->method('POST')->uri($uri);
    }

    public static function put(string $uri): static
    {
        return (new static())->method('PUT')->uri($uri);
    }

    public static function patch(string $uri): static
    {
        return (new static())->method('PATCH')->uri($uri);
    }

    public static function delete(string $uri): static
    {
        return (new static())->method('DELETE')->uri($uri);
    }

    public static function head(string $uri): static
    {
        return (new static())->method('HEAD')->uri($uri);
    }

    public static function options(string $uri): static
    {
        return (new static())->method('OPTIONS')->uri($uri);
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function uri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    public function headers(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function body(mixed $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function requestOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @throws HttpRequestException
     */
    public function send(): array
    {
        $ch = curl_init();

        $url = $this->uri;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, Request::agent());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        if ($this->method === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        } else if ($this->method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }

        if (!empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($this->options)) {
            curl_setopt_array($ch, $this->options);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $content = substr($response, $header_size);

        curl_close($ch);

        if ($error) {
            throw new HttpRequestException($error);
        }
        return ['headers' => $headers, 'content' => $content, "full_response" => $response, "curl" => $ch];
    }
}