<?php

declare(strict_types=1);

namespace Az\Route\Middleware;

use Az\Route\Route;
use HttpSoft\Runner\MiddlewareResolverInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use DI\FactoryInterface;

final class RouteDispatchMiddleware implements MiddlewareInterface
{  
    /**
     * @var MiddlewareResolverInterface
     */
    private MiddlewareResolverInterface $resolver;
    private ContainerInterface|InvokerInterface|FactoryInterface $container;

    /**
     * @param MiddlewareResolverInterface $resolver
     */
    public function __construct(
        MiddlewareResolverInterface $resolver, 
        ContainerInterface $container)
    {
        $this->resolver = $resolver;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedAssignment
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$route = $request->getAttribute(Route::class)) {
            return $handler->handle($request);
        }

        $routeHandler = $this->resolve($request, $route->getHandler());
        $middleware = $this->resolver->resolve($routeHandler);
        return $middleware->process($request, $handler);
    }

    private function resolve(&$request, $handler)
    {       
        $handler = $this->parseHandlerString($handler);

        if (is_array($handler) && isset($handler[1]) && is_string($handler[1])) {
            $controller = $this->container->get($handler[0]);

            if ($controller instanceof MiddlewareInterface || $controller instanceof RequestHandlerInterface) {
                $request = $request->withAttribute('action', $handler[1]);
                return $controller;
            }

            return [$this->container->get($handler[0]), $handler[1]];
        }

        if (is_string($handler) && class_exists($handler)) {
            return $this->container->get($handler);
        }
        
        return $handler;
    }

    private function parseHandlerString($handler)
    {
        if (!is_string($handler)) {
            return $handler;
        }

        if (strpos($handler, '::')) {
            return explode('::', $handler);
        }

        if (strpos($handler, '@')) {
            return explode('@', $handler);
        }

        return $handler;
    } 
}
