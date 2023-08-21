<?php

namespace Sys\Console;

use League\CLImate\CLImate;

final class Help extends AbstractConsole
{
    private array $config = [];

    public function __invoke()
    {
        $config = CONFIGPATH . 'console.php';

        if (is_file($config)) {
            $this->config += require $config;
        }

        $this->climate->out('Usage: php cli <command> [args...]')->br();

        foreach ($this->config as $key => $val) {
            if (isset($val['args'])) {
                $str1 = $key . ' OR ' . implode(' OR ', $val['args']);
            } else {
                $str1 = $key;
            }

            $str2 = (isset($val['desc'])) ? $str2 = $val['desc'] : 'Enter "' . $key . ' help" - to learn more';
            $out[] = ["-\t", $str1, $str2];
        }

        $this->climate->columns($out)->br();
    }
}
