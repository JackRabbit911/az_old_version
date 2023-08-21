<?php

namespace Sys\Console;

use League\CLImate\CLImate;

abstract class AbstractConsole
{
    protected CLImate $climate;
    protected $curlinfo;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    protected function curl($path)
    {
        $path = ltrim($path, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "localhost/console/$path");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/plain']);
        $result = curl_exec($ch);
        $this->curlinfo = curl_getinfo($ch);
        curl_close($ch);

        return $result;
    }

    protected function parseArgs($pattern, $replace, $args)
    {
        $p = '/^-[' . $pattern . ']+$/';
        if (preg_match($p, $args)) {
            if (strlen($args) > 2) {
                $flags = str_split($args);
                array_shift($flags);
                array_walk($flags, function (&$v) {
                    $v = '-' . $v;
                });
                $args = $flags;
            }
        }

        if (!is_array($args)) {
            $args = [$args];
        }

        foreach ($replace as $key => $arr) {
            if (!empty(array_intersect($arr, $args))) {
                $result[] = $key;
            }
        }

        return $result ?? $args;
    }
}
