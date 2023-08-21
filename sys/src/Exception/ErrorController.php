<?php

namespace Sys\Exception;

use Az\Route\Route;
use Sys\Exception\ExceptionResponseFactory;
use Sys\Helper\Facade\Http;

final class ErrorController
{
    private ExceptionResponseFactory $factory;

    public function __construct(ExceptionResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke($request)
    {
        $code = (integer) $request->getAttribute(Route::class)->getParameters()['code'];
        $responseType = Http::getResponseType();
        return $this->factory->createResponse($responseType, $code);
    }
}
