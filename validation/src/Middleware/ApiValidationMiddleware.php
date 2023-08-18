<?php

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use HttpSoft\Response\JsonResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ApiValidationMiddleware implements MiddlewareInterface
{
    protected Validation $validation;
    protected ContainerInterface $container;

    public function __construct(Validation $validation, ContainerInterface $container)
    {
        $this->validation = $validation;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setRules();

        if (($response = $this->validate($request, $handler))) {
            return $response;
        }

        return new JsonResponse($this->validation->getResponse());
    }

    protected function setRules() {}

    protected function validate(ServerRequestInterface $request, RequestHandlerInterface $handler): ?ResponseInterface
    {
        $data = $request->getBody()->getContents();
        $data = json_decode($data, true)??[];
        $files = $request->getUploadedFiles();

        if ($this->validation->check($data, $files)) {
            return $handler->handle($request->withAttribute('post', $data));
        }

        return null;
    }
}
