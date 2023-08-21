<?php

namespace Sys\Helper\Facade;

abstract class FacadeAbstract
{
    protected static $instance;
    protected static $class;

    public static function __callStatic($name, $arguments)
    {
        global $container;

        $class = str_replace('\Facade', '',  get_called_class());

        if (!isset(static::$instance[$class])) {
            if ($container && $container->has($class)) {
                static::$instance[$class] = $container->get($class);
            } else {
                static::$instance[$class] = new $class;
            }
        }

        return static::$instance[$class]->$name(...$arguments);
    }
}
