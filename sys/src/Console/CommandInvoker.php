<?php

namespace Sys\Console;

use League\CLImate\CLImate;

final class CommandInvoker
{
    private CLImate $climate;
    private array $config = [
        'help' => [
            'command' => ['-h', '--help'],
            'handler' => Help::class,
        ],
    ];

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
        $config = CONFIGPATH . 'console.php';

        if (is_file($config)) {
            $this->config += require $config;
        }
    }

    public function __invoke($command = 'help', $action = null)
    {
        if (!$command) {
            $command = 'help';
            // $this->climate->to('error')->red('Command is required. Enter "php cli -h" to learn more..');
            // exit;
        }

        foreach ($this->config as $key => $val) {
            if ($key === $command || (isset($val['command']) && in_array($command, $val['command']))) {
                $handler = (isset($val['handler'])) ? $val['handler'] : $val;
                if (isset($val['methods'])) {
                    foreach ($val['methods'] as $k => $v) {
                        if ($k === $action || in_array($action, $v)) {
                            $method = $k;
                            break;
                        }
                    }
                }

                global $container;
                $handler = $container->get($handler);

                if (!isset($method)) {
                    if (method_exists($handler, $action) && is_callable([$handler, $action])) {
                        $method = $action;
                    } elseif (method_exists($handler, '__invoke')) {
                        $method = '__invoke';
                        global $argv;
                        array_unshift($argv, $action);
                    } else {
                        $this->climate->to('error')->red("Method $action is not found");
                        exit;
                    }
                }

                return [$handler, $method];
            }
        }

        $this->climate->to('error')->red('Command not recognized');
        exit;
    }
}
