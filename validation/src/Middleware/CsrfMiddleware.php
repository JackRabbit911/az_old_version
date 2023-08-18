<?php

namespace Az\Validation\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
            $data = $request->getParsedBody();

            if (!isset($data['_csrf'])) {
                throw new InvalidArgumentException("Token not found", 419);
            }
            
            $session = $request->getAttribute('session');
            $sessToken = $session->get('_csrf');
            $formToken = $data['_csrf'];

            if ($sessToken !== $formToken) {
                throw new InvalidArgumentException("Token not match", 419);
            }

            unset($data['_csrf']);
            $request = $request->withParsedBody($data);
        }

        return $handler->handle($request);
    }
}
