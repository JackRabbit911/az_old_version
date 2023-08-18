<?php

namespace Az\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Session\Session;

class SessionCommitMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');

        if ($session instanceof Session) {
            $session->commit();
            $request = $request->withAttribute('session', $session);
        }
        
        return $handler->handle($request);
    }
}
