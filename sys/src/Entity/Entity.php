<?php

namespace Sys\Entity;

use Exception;

abstract class Entity
{
    protected array $data = [];

    public function __construct(?array $data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    public function toArray()
    {
        $vars = get_object_vars($this);
        $data = $vars['data'] ?? [];
        unset($vars['data']);
        return $vars + $data;
    }

    public function set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        } else {
            $this->data[$key] = $value;
        }

        if (isset($GLOBALS['_changed']) && in_array($this, $GLOBALS['_changed'])) {
            return;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];

        if ($trace['class'] === 'PDOStatement' && strpos($trace['function'], 'fetch') === 0) {
            return;
        }

        $GLOBALS['_changed'][] = $this;
    }

    public function __toString()
    {
        return spl_object_hash($this);
    }

    public function __isset($name)
    {
        return (isset($this->$name) || isset($this->data[$name]));
    }

    public function __unset($name)
    {
        unset($this->$name);
        unset($this->data[$name]);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function &__get($key): mixed
    {
        $null = null;

        if (property_exists($this, $key)) {
            return $this->$key;
        } elseif (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } elseif (env()->env >= TESTING) {
            throw new Exception(sprintf('property "%s" is not defined', $key));
        } else {
            return $null;
        }
    }

    public function __call($name, $arguments)
    {
        return $this->$name ?? $this->data[$name] ?? null;
    }
}
