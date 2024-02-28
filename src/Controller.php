<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 06:53
 */


namespace Router;

use Router\Enums\HttpStatus;
use Router\Exceptions\HttpException;

abstract class Controller
{
    protected array $params = [];
    protected ?Request $request;
    protected ?Response $response;

    public function __construct(array $params = [], ?Request $request = null, ?Response $response = null)
    {
        $this->params = $params;
        $this->request = $request ?? new Request();
        $this->response = $response ?? new Response();
    }

    /**
     * @throws HttpException
     */
    public function any(): mixed
    {
        if (method_exists($this, 'get') && $this->request::method() === 'GET') {
            return $this->get();
        } elseif (method_exists($this, 'create') && $this->request::method() === 'POST') {
            return $this->create();
        } elseif (method_exists($this, 'update') && $this->request::method() === 'PUT') {
            return $this->update();
        } elseif (method_exists($this, 'delete') && $this->request::method() === 'DELETE') {
            return $this->delete();
        } elseif ($this->request::method() !== 'OPTIONS' && $this->request::method() !== 'HEAD') {
            throw new HttpException(HttpStatus::METHOD_NOT_ALLOWED);
        }
        return false;
    }
}