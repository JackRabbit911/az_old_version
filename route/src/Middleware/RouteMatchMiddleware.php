<?php

declare(strict_types=1);

namespace Az\Route\Middleware;

use Az\Route\Route;
use Az\Route\RouteCollection;
use Az\Route\RouteCollectionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Runner\MiddlewarePipelineInterface;

use function array_filter;
use function array_unique;
use function implode;
use function in_array;
use function is_string;
use function strtoupper;

final class RouteMatchMiddleware implements MiddlewareInterface
{
    private MiddlewarePipelineInterface $pipeline;
    /**
     * @var RouteCollecton
     */
    private RouteCollection $router;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    private array $allowedMethods = [];

    public function __construct(RouteCollectionInterface $route)
    {
        $this->router = $route;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$route = $this->router->match($request)) {
            return $handler->handle($request);
        }

        return $handler->handle($request->withAttribute(Route::class, $route));
    }

    private function getEmptyResponseWithAllowedMethods(array $methods): ResponseInterface
    {
        foreach ($this->allowedMethods as $method) {
            if (is_string($method)) {
                $methods[] = $method;
            }
        }

        $methods = implode(', ', array_unique(array_filter($methods)));
        return $this->responseFactory->createResponse(405)->withHeader('Allow', $methods);
    }

    private function isAllowedMethods(string $method): bool
    {
        return ($this->allowedMethods !== [] && in_array(strtoupper($method), $this->allowedMethods, true));
    }
}
