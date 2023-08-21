<?php

namespace Sys\Migrations;

use League\CLImate\CLImate;
use Sys\Console\AbstractConsole;

final class Console extends AbstractConsole
{
    private File $file;

    public function __construct(CLImate $climate, File $file)
    {
        parent::__construct($climate);
        $this->file = $file;
    }

    public function create($tableName, $path = '')
    {
        $this->createFile('create', $tableName, $path);
    }

    public function alter($tableName, $path = '')
    {
        $this->createFile('alter', $tableName, $path);   
    }

    public function list($path = '')
    {
        if (!empty($path)) {
            $path = '/' . $path;
        }

        $list = $this->curl('migrate/list' . $path);

        if ($this->curlinfo['http_code'] !== 200) {
            $this->climate->out($list);
        } else {
            list($up, $down) = json_decode($list, true);
            $this->climate->lightGreen()->out(array_reverse($up));
            $this->climate->lightYellow()->out(array_reverse($down));
        }
    }

    public function up($path = '')
    {
        if (!empty($path)) {
            $path = '/' . $path;
        }

        $result = $this->curl('/migrate/up' . $path);

        if ($this->curlinfo['http_code'] !== 200) {
            $this->climate->out($result);
        } else {
            if ($result === 'done') {
                $this->climate->out('All migrations up is already done');
            } elseif ($result === 'error') {
                $this->climate->to('error')->red('Something went wrong...');
            } else {
                $this->climate->out('Migration UP completed successfully!');
                $result = json_decode($result);
                $this->climate->out($result);
            }
        }
    }

    public function down($path = '')
    {
        if (!empty($path)) {
            $path = '/' . $path;
        }

        $result = $this->curl('/migrate/down' . $path);

        if ($this->curlinfo['http_code'] !== 200) {
            $this->climate->out($result);
        } else {
            if ($result === 'done') {
                $this->climate->out('All migrations down is already done');
            } else {
                $this->climate->out('Migration DOWN completed successfully! ' . $result);
            }
        }
    }

    private function createFile($action, $tableName, $path)
    {
        $fn = $this->file->createFile($action, $tableName, $path);
        
        if ($fn) {
            $this->climate->out('<light_green>Created file</light_green> ' . $fn);
        } else {
            $this->climate->to('error')->red('Failed to write migration file');
        }
    }
}
