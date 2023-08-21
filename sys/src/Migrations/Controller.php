<?php

namespace Sys\Migrations;

use Sys\Controller\BaseController;

final class Controller extends BaseController
{
    private Model $model;
    private File $file;

    public function __construct(Model $model, File $file)
    {
        $this->model = $model;
        $this->file = $file;
    }

    public function list($path = '')
    {
        return $this->updown($path);
    }

    public function up($path = '')
    {
        list($up, $down) = $this->updown($path);

        if (empty($up)) {
            return 'done';
        }

        $result = $this->model->up($up, $path);

        if (!$result) {
            return 'error';
        }

        return $result;
    }

    public function down($path = '')
    {
        list($up, $down) = $this->updown($path);
        return ($down) ? $this->model->down(array_pop($down), $path) : 'done';
    }

    private function updown($path = '')
    {
        $files = $this->file->list($path);
        $down = $this->model->get($path);
        $up = array_diff($files, $down);

        natsort($up);
        natsort($down);

        return [$up, $down];
    }
}
