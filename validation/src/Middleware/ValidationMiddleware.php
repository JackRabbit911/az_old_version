<?php

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use HttpSoft\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ValidationMiddleware implements MiddlewareInterface
{
    protected Validation $validation;
    protected ?string $path = null;

    public function __construct(Validation $validation)
    {
        $this->validation = $validation;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setPath();
        $path = rawurldecode(rtrim($request->getUri()->getPath(), '/'));

        if ($this->path && $this->path !== $path) {           
            return $handler->handle($request);
        }
        
        $this->setRules();
        $data = ($request->getMethod() === 'GET') ? $request->getQueryParams() : $request->getParsedBody();

        if (($response = $this->validate($request, $handler, $data))) {
            return $response;
        }

        return $this->errorHandler($request, $data);
    }

    protected function setPath() {}

    protected function setRules() {}

    protected function modifyData(& $data) {}

    protected function validate(ServerRequestInterface $request, RequestHandlerInterface $handler, array $data): ?ResponseInterface
    {
        $files = $request->getUploadedFiles();

        if ($this->validation->check($data, $files)) {
            $this->modifyData($data);
            return $handler->handle($request
                ->withParsedBody($data)
                ->withAttribute('validation', $this->validation));
        }

        return null;
    }

    protected function errorHandler(ServerRequestInterface $request, array $data): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $session->flash('validation', $this->validation->getResponse());
        return new RedirectResponse($request->getServerParams()['HTTP_REFERER'], 302);
    }
}
