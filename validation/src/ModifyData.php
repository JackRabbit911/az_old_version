<?php

namespace Az\Validation;

trait ModifyData
{
    private function modifyData($data)
    {
        $flatten = function ($data, $prefix = null) use (&$flatten) {
            $result = [];

            foreach ($data as $key => $value) {
                if ($prefix) {
                    $key = $prefix . '[' . $key . ']';
                }

                if (is_array($value) && !array_is_list($value)) {
                    $result = array_merge($result, $flatten($value, $key));
                } else {
                    $result[$key] = $value;
                }
            }

            return $result;
        };

        $data = $flatten($data);

        foreach (array_keys(array_diff_key($this->validator, $data)) as $key) {
            $data[$key] = null;
        }

        return $data;
    }
}
