<?php

namespace Sys\I18n;

use Az\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Response\RedirectResponse;

final class I18nMiddleware implements MiddlewareInterface
{
    private I18n $i18n;
    private array $segments;

    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);
        $params = $route->getParameters();

        $path = ltrim($request->getUri()->getPath(), '/');
        $pos = strpos($path, $this->i18n->baseLang());

        if ($this->i18n->getRedirect() === I18n::LANG_TO_EMPTY) {
            if ($pos === 0) {
                $params['lang'] = '';
                $uri = $route->path($params);
                return new RedirectResponse($uri);
            }
        }

        if ($this->i18n->getRedirect() === I18n::EMPTY_TO_LANG) {
            if ($pos === false) {
                $params['lang'] = $this->i18n->baseLang();
                $uri = $route->path($params);
                return new RedirectResponse($uri);
            }
        }

        $this->i18n->lang($params['lang']);

        foreach ($this->i18n->list() as $lang => $language) {
            $params['lang'] = $this->i18n->langSegment($lang);
            $href = $route->path($params);
            $links[$href] = $language; 
        }

        $this->i18n->setVar('linksList', $links);

        return $handler->handle($request->withAttribute('i18n', $this->i18n));
    }
}
