<?php

namespace Az\Validation;

use Closure;
use ReflectionMethod;
use ReflectionFunction;

final class Message
{
    public array $keys = [];
    private array $msgPath = ['messages'];
    private array $messages = [];
    private string $lang = 'en';

    public function addMsgPath(string $path): void
    {
        array_push($this->msgPath, $path);
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function get(string $key, array $params = [], string $default = 'default'): string
    {
        $this->setMessages();

        $search = array_keys($params);
        $replace = array_values($params);
        

        if (strpos($key, ' ') !== false) {
            return str_replace($search, $replace, $key);
        }

        $message = $this->messages[$key] ?? $this->messages[$default] ?? 'Invalid data';
        return str_replace($search, $replace, $message);
    }

    public function setMsgKey($name, $key)
    {
        $this->keys[$name] = $key;
    }

    private function setMessages()
    {
        foreach ($this->msgPath as $path) {
            $file = trim($path, '/') . '/' . $this->lang . '.php';
            $this->messages = array_replace($this->messages, require $file);
        }
    }

    // private function getKeyParams($name, $e)
    // {
    //     if (is_array($e->handler) && method_exists($e->handler[0], $e->handler[1])) {
    //         $reflect = new ReflectionMethod($e->handler[0], $e->handler[1]);
    //     } elseif (is_string($e->handler)) {
    //         if (function_exists($e->handler)) {
    //             $reflect = new ReflectionFunction($e->handler);
    //         } else {
    //             $reflect = new ReflectionMethod($e->handler);
    //         } 
    //     } elseif ($e->handler instanceof Closure) {
    //         $reflect = new ReflectionFunction($e->handler);
    //     }

    //     if (isset($reflect)) {
    //         $msgKey = $this->keys[$name] ?? $reflect->getShortName();

    //         foreach ($reflect->getParameters() as $k => $refParam) {
    //             $key = ':' . $refParam->getName();

    //             if (!array_key_exists($k, $e->params)) {
    //                 if ($refParam->isDefaultValueAvailable()) {
    //                     $value = $refParam->getDefaultValue();
    //                 }
    //             } else {
    //                 $value = $e->params[$k];
    //             }

    //             if (isset($value) && is_scalar($value)) {
    //                 $result[$key] = $value;
    //             }
    //         }

    //         $result[':name'] = $name;

    //         $msgParams = $result ?? [];
    //     } else {
    //         $msgKey = $this->keys[$name] ??  $e->handler[1] ?? 'default';
    //         $msgParams = [];
    //     }

    //     if (isset($e->key) && is_string($e->key)) {
    //         $msgKey = $e->key;
    //     }

    //     return [$msgKey, $msgParams];
    // }
}
