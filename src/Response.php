<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 06:17
 */


namespace Router;

use Router\Enums\HttpStatus;

class Response
{
    protected HttpStatus $httpStatus = HttpStatus::OK;
    protected bool $success = true;
    protected bool $cache = false;
    private array $messages = [];
    private array $data = [];

    public function getHttpStatus(): HttpStatus
    {
        return $this->httpStatus;
    }

    public function setHttpStatus(HttpStatus $httpStatus): static
    {
        $this->httpStatus = $httpStatus;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->getHttpStatus()->value;
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;
        return $this;
    }

    public function getCache(): bool
    {
        return $this->cache;
    }

    public function setCache(bool $cache): static
    {
        $this->cache = $cache;
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): static
    {
        $this->messages = $messages;
        return $this;
    }

    public function addMessage(string $message): static
    {
        $this->messages[] = $message;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function send(): void
    {
        if ($this->getCache()) {
            header('Cache-Control: max-age=60');
        } else {
            header('Cache-Control: no-cache, no-store');
        }
        if (is_numeric($this->getStatusCode())) {
            http_response_code($this->getStatusCode());
        }
        if (Request::method() !== 'OPTIONS' && Request::method() !== 'HEAD') {
            $response = [
                'code' => $this->getStatusCode(),
                'success' => $this->getSuccess(),
                'messages' => $this->getMessages(),
                'data' => $this->getData()
            ];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        die();
    }

}