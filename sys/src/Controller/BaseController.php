<?php

namespace Sys\Controller;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Route;

abstract class BaseController implements MiddlewareInterface
{
    use InvokeTrait;

    protected ServerRequestInterface $request;
    private string $action;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        $this->action = $request->getAttribute('action', '__invoke');

        $this->_before();
        $response = $this->call($this->action, $request->getAttribute(Route::class)->getParameters());
        $this->_after($response);
        return $response;
    }

    protected function _before() {}

    protected function _after(&$response) {}
}
