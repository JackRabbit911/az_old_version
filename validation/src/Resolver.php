<?php

namespace Az\Validation;

use ReflectionMethod;
use ReflectionFunction;
use InvalidArgumentException;

final class Resolver
{
    private ValidationHandler $defaultHandler;

    public $params;

    public function __construct(ValidationHandler $handler)
    {
        $this->defaultHandler = $handler;
    }

    public function resolve($handler)
    {
        if (is_callable($handler)) {
            return $handler;
        }


        if (is_string($handler)) {
            if ($this->defaultHandler->_is_callable($handler)) {
                return [$this->defaultHandler, $handler];
            }
        }

        if (is_array($handler)) {
            $handler = $handler[1];
        }

        throw new InvalidArgumentException(
            sprintf('Function "%s" is not callable', $handler)
        );
    }

    private function setReflectionRarameters($handler, $params)
    {
        if (is_array($handler) && method_exists($handler[0], $handler[1])) {
            $refMethod = new ReflectionMethod($handler[0], $handler[1]);
        } elseif (is_string($handler)) {
            if (function_exists($handler)) {
                $refMethod = new ReflectionFunction($handler);
            } else {
                $refMethod = new ReflectionMethod($handler);
            }           
        }

        if (isset($refMethod)) {
            foreach ($refMethod->getParameters() as $k => $refParam) {
                $key = ':' . $refParam->getName();

                if (!array_key_exists($k, $params)) {
                    if ($refParam->isDefaultValueAvailable()) {
                        $value = $refParam->getDefaultValue();
                    }
                } else {
                    $value = $params[$k];
                }

                if (isset($value) && is_scalar($value)) {
                    $result[$key] = $value;
                }
            }
        } 
    }
}
