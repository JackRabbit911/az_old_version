<?php

namespace Sys\Exception;

use League\CLImate\CLImate;
use Sys\Console\AbstractConsole;

final class Console extends AbstractConsole
{
    private $file = 'app/storage/error.log';
    private $content;

    public function __construct(CLImate $climate)
    {
        parent::__construct($climate);       
        $this->content = file($this->file);
    }

    public function show($count = 2)
    {
        $slice = array_slice($this->content, -$count, $count);
        $this->climate->out($slice);
    }

    public function gc($count = 0)
    {
        $slice = array_slice($this->content, -$count, $count);
        $content = implode('', $slice);
        file_put_contents($this->file, $content);

        $co = count(file($this->file));

        $this->climate->out('File ' . $this->file . ' contains ' . $co . ' entries');
    }
}
