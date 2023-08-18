<?php

namespace Az\Validation;

trait SantizeParams
{
    private function santizeParams(array $params, $value): array
    {
        $is_value = false;

        foreach ($params as &$param) {
            switch ($param) {
                case ':validation':
                    $param = $this->validation;
                    break;
                case ':data':
                    $param = $this->validation->data;
                    break;
                case ':value':
                    $param = $value;
                    $is_value = true;
                    break;
                default:
                    if (is_string($param) && strpos($param, ':') === 0) {
                        $key = substr($param, 1);
                        if (array_key_exists($key, $this->validation->data)) {
                            $param = $this->validation->data[$key];
                        }
                    }
            }
        }

        if (!$is_value) {
            array_unshift($params, $value);
        }

        return $params;
    }
}
