<?php

namespace Az\Route\Middleware;

use Psr\Container\ContainerInterface;
use DI\FactoryInterface;

class HandlerResolver
{
    private ContainerInterface|FactoryInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve($handler)
    {
        $handler = $this->parseHandlerString($handler);

        if (is_array($handler) && isset($handler[1]) && is_string($handler[1])) {

            if (method_exists($handler[0], 'process') || method_exists($handler[0], 'handle')) {           
                // return new $handler[0]($this->container, $handler[1]);
                return $this->container->make($handler[0], ['action' => $handler[1]]);
            } else {
                return [$this->container->get($handler[0]), $handler[1]];
            }
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
