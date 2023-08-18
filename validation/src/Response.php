<?php

namespace Az\Validation;

use Closure;
use ReflectionMethod;
use ReflectionFunction;

final class Response
{
    private Message $msg;
    private array $response = [];
    private array $msgKeys = [];
    private array $messages = [];

    public function __construct(Message $msg)
    {
        $this->msg = $msg;
    }

    public function get($validator, $data, $files = [])
    {
        $keys = array_merge(array_keys($data), array_keys($files), array_keys($validator));
        
        foreach ($keys as $name) {
            if (isset($validator[$name]->e)) {
                list($key, $params) = $this->getKeyParams($name, $validator[$name]->e);

                if (isset($this->messages[$name])) {
                    $msg[$name] = strtr($this->messages[$name], $params);
                }

                $this->response[$name] = [
                    'status' => 'error',
                    'value' => '',
                    'msg' => $msg[$name] ?? $this->msg->get($key, $params),
                ];

            } elseif (!is_array($data[$name]) || (is_array($data[$name]) && array_is_list($data[$name]))) {
                $this->response[$name] = [
                    'status' => 'success',
                    'value' => $data[$name] ?? '',
                    'msg' => $this->msg->get('success'),
                ];
            }
        }

        return $this->response;
    }

    public function setMsgKey($name, $key)
    {
        $this->msgKeys[$name] = $key;
    }

    public function setMessage($name, $msg)
    {
        $this->messages[$name] = $msg;
    }

    public function addMsgPath($path)
    {
        $this->msg->addMsgPath($path);
    }

    public function setLang($lang)
    {
        $this->msg->setLang($lang);
    }

    private function getKeyParams($name, $e)
    {
        if (is_array($e->handler) && method_exists($e->handler[0], $e->handler[1])) {
            $reflect = new ReflectionMethod($e->handler[0], $e->handler[1]);
        } elseif (is_string($e->handler)) {
            if (function_exists($e->handler)) {
                $reflect = new ReflectionFunction($e->handler);
            } else {
                $reflect = new ReflectionMethod($e->handler);
            } 
        } elseif ($e->handler instanceof Closure) {
            $reflect = new ReflectionFunction($e->handler);
        }

        if (isset($reflect)) {
            $msgKey = $this->msgKeys[$name] ?? $reflect->getShortName();

            foreach ($reflect->getParameters() as $k => $refParam) {
                $key = ':' . $refParam->getName();

                if (!array_key_exists($k, $e->params)) {
                    if ($refParam->isDefaultValueAvailable()) {
                        $value = $refParam->getDefaultValue();
                    }
                } else {
                    $value = $e->params[$k];
                }

                if (isset($value) && is_scalar($value)) {
                    $result[$key] = $value;
                }
            }

            $result[':name'] = $name;

            $msgParams = $result ?? [];
        } else {
            $msgKey = $this->msgKeys[$name] ??  $e->handler[1] ?? 'default';
            $msgParams = [];
        }

        if (isset($e->key) && is_string($e->key)) {
            $msgKey = $e->key;
        }

        return [$msgKey, $msgParams];
    }
}
