<?php

namespace Az\Route;

use Sys\DefaultHandler;
use DI\Container;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Invoker
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function invoke($request, $handler, $params)
    {
        $controller = $handler[0];
        $action = $handler[1];
        $interfaces = class_implements($controller);
        $defaultHandler = $this->container->get(DefaultHandler::class);
        $request = $request->withAttribute('action', $action);

        if (isset($interfaces[MiddlewareInterface::class])) {                
            $response = $this->container->call([$controller, 'process'], [$request, $defaultHandler]);
        } elseif (isset($interfaces[RequestHandlerInterface::class])) {
            $response = $this->container->call([$controller, 'handle'], [$request]);
        } else {
            $response = $this->container->call([$controller, $action], $params);
        }

        if (is_string($response)) {
            return new HtmlResponse($response);
        } elseif (is_array($response)) {
            return new JsonResponse($response);
        } else {
            return $response;
        }
    }
}
