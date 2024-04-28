<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 06:17
 */


namespace Router;

use Router\Enums\HttpStatus;
use Router\Traits\Views;

class Response
{
    use Views;

    protected array $cors = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'OPTIONS, HEAD, GET, POST, PUT, PATCH, DELETE',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Accept',
        'Access-Control-Expose-Headers' => '',
        'Access-Control-Max-Age' => '60',
        'Access-Control-Allow-Credentials' => 'false',
    ];
    protected array $headers = [];
    protected string $content = '';
    protected HttpStatus $status = HttpStatus::OK;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function cors(string $name, string $value): static
    {
        $this->cors[$name] = $value;
        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function status(HttpStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function redirect(string $url, bool $permanent = false): static
    {
        $this->header('Location', $url);
        $this->status($permanent ? HttpStatus::MOVED_PERMANENTLY : HttpStatus::FOUND);
        return $this;
    }

    public function json(array $data, int $flags = 0): static
    {
        $this->header('Content-Type', 'application/json');
        $this->content = json_encode($data, $flags);
        return $this;
    }

    public function view(string $view, array $params = [], ?string $layout = null): static
    {
        $this->header('Content-Type', 'text/html');
        $this->content($this->render($view, $params, $layout));
        return $this;
    }

    protected function sendHeaders(): void
    {
        foreach ($this->cors as $name => $value) {
            $this->header($name, $value);
        }
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }

    public function send(): string
    {
        http_response_code($this->status->value);

        $this->sendHeaders();

        return $this->content;
    }
}