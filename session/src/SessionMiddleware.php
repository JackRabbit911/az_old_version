<?php

namespace Az\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Session\Session;

final class SessionMiddleware implements MiddlewareInterface
{
    private Session $session;

    public function __construct(Session $session)
    {       
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user_id = (isset($_COOKIE[$this->session->getCookieName()])) ? $this->session->user_id : null;
        $request = $request->withAttribute('session', $this->session)->withAttribute('user_id', $user_id);
        return $handler->handle($request);
    }
}
