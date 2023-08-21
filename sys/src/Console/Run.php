<?php

namespace Sys\Console;

final class Run extends AbstractConsole
{
    public function __invoke($path)
    {
        $response = $this->curl($path);
        $this->climate->out($response)->br();
    }
}
