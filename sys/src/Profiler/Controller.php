<?php

namespace Sys\Profiler;

use Sys\Controller\WebController;
use Sys\Profiler\Model\ProfilerModelInterface;

final class Controller extends WebController
{
    public function __invoke(ProfilerModelInterface $model, $uri = '/')
    {
        $query = $this->request->getUri()->getQuery();

        if (!empty($query)) {
            $query = '?' . $query;
        }

        $uri = '/' . ltrim($uri, '/') . $query;
        $data = $model->get($uri);

        $data->size = $this->bytes($data->size);
        $data->time = round($data->time, 2) . ' ms';
        $data->memory = $this->bytes($data->memory);
        $data->profiles = json_decode($data->profiles);
        $data->queries = count($data->profiles);
        $data->duration = round($data->duration, 2) . ' ms';

        return ($data) ? render('sys/src/Profiler/view.php', (array) $data) : '';
    }

    private function bytes($count)
    {
        $i = 0;
        while (floor($count / 1024) > 0) {
            $i++;
            $count /= 1024;
        }
        
        $name = array('byte', 'KB', 'MB');
        return round($count, 2) . ' ' . $name[$i];
    }
}
