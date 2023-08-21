<?php

namespace Sys\Controller;

use Psr\Http\Message\ResponseInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;

trait InvokeTrait
{
    private function call(string $action, array $attr = [])
    {
        global $container;

        if ($container && method_exists($container, 'call')) {
            $result = $container->call([$this, $action], $attr);
        } else {
            $args = [];
            $reflect = new \ReflectionMethod($this, $action);
            foreach ($reflect->getParameters() as $param) {
                $name = $param->getName();
                $args[$name] = $attr[$name] ?? $param->getDefaultValue() ?? null;
            }
            $result = $reflect->invokeArgs($this, $args);
        }

        return $this->normalizeResponse($result);
    }

    private function normalizeResponse($response): ResponseInterface
    {
        if (is_string($response) || is_numeric($response)) {
            return new HtmlResponse($response);
        }
    
        if (is_array($response) || is_null($response)) {
            return new JsonResponse($response);
        }
    
        return $response;
    }
}
