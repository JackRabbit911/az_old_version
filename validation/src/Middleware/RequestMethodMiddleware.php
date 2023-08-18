<?php

namespace Az\Validation\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestMethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            if (isset($data['_method'])) {
                $method = strtoupper($data['_method']);

                if (!in_array($method, ['PUT', 'PATCH', 'DELETE', 'OPTIONS', 'POST'])) {
                    throw new InvalidArgumentException('Method Not Allowed', 405);
                }

                unset($data['_method']);
                $request = $request->withMethod($method)->withParsedBody($data);
            }            
        }

        return $handler->handle($request);
    }
}
