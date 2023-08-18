<?php

namespace Az\Session;

use Sys\Console\AbstractConsole;
use Sys\Helper\Facade\Http;

class Console extends AbstractConsole
{
    public function gc()
    {
        $path = "/session/gc";
        $result = $this->curl($path);

        if ($this->curlinfo['http_code'] === 200) {
            $this->climate->out("$result files was deleted");
        } else {
            $this->climate->out($result);
        }
    }

    public function help()
    {
        $this->climate->out ('Usage: cli session
        enter session gc OR -s gc OR --sess gc to for garbage cleaning');
    }
}
