<?php

namespace Sys\Helper;

final class Form
{
    public function getSearchArray(string $str): array
    {
        $array = explode(' ', trim($str));
        $result = [];

        foreach ($array as $word) {
            $word = trim($word);
             if (mb_strlen($word) > 3)  {
                $result[] = $word;
             }
        }

        return $result;
    }

    public function str2intAraay(array $tags): array
    {
        $result = array_map(function($a) {
            return (integer) $a;
        }, $tags);

        return $result;
    }
}
