<?php

namespace Sys;
use Psr\Http\Message\ResponseInterface;

class PostProcess
{
    public function process(ResponseInterface $response, string $mode)
    {
        $config = CONFIGPATH . 'post_process.php';
        if (!is_file($config)) {
            return $response;
        }

        global $container;

        $queue = require_once $config;

        foreach ($queue as $task) {
            $result = $container->call($task, ['response' => $response, 'mode' => $mode]);

            if ($result instanceof ResponseInterface) {
                $response = $result;
            }
        }

        return $response;
    }
}
