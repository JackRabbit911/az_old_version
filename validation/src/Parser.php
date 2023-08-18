<?php

namespace Az\Validation;

final class Parser
{
    public function parse($handler, $params)
    {
        if (is_string($handler)) {
            foreach (explode('|', str_replace(' ', '', $handler)) as $item) {
                $res[] = $this->parseStr($item, $params);
            }

            return $res;
        }

        return [(object) ['handler' => $handler, 'params' => $params, 'inverse' => false]];
    }

    private function parseStr($handler, $params = [])
    {
        if (strpos($handler, ')', -1) === strlen($handler)-1) {
            $pattern = '/\((.*)\)/';
            if(preg_match($pattern, $handler, $match))
                if($match[1]) $params = array_merge(explode(',', $match[1]), $params);
            
            $handler = preg_replace($pattern, '', $handler);                 
        }

        if ($handler[0] === '!') {
            $handler = substr($handler, 1);
            $inverse = true;
        } else {
            $inverse = false;
        }

        return (object) ['handler' => $handler, 'params' => $params, 'inverse' => $inverse];
    }
}
