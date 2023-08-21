<?php

namespace Sys;

use Sys\Exception\ExceptionResponseFactory;
use Sys\Helper\Facade\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultHandler implements RequestHandlerInterface
{
    private $factory;
    private $responseType;

    public function __construct(ExceptionResponseFactory $factory, ?string $responseType = null)
    {
        $this->factory = $factory;
        $this->responseType = ($responseType) ? $responseType : Http::getResponseType();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->createResponse($this->responseType, 404);
    }
}
