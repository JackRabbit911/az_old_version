<?php

namespace Sys\Profiler;

use Az\Route\RouteCollectionInterface;
use HttpSoft\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Sys\Profiler\Model\ProfilerModelInterface;

final class Profiler
{
    private int $start;
    private $memory;
    private ProfilerModelInterface $model;

    public function __construct(ProfilerModelInterface $model, RouteCollectionInterface $route)
    {
        $this->start = hrtime(true);
        $this->memory = memory_get_usage();
        $this->model = $model;
        $this->model->setProfiling();

        if (env()->env >= TESTING) {
            $route->get('/_profiler/{uri?}', Controller::class)
                ->tokens(['uri' => '.*']);
        }
    }

    public function __invoke(ResponseInterface $response, string $mode)
    {
        // dd($this->model->showProfiles());
        $stream = $response->getBody();
        $size = $stream->getSize();

        
        
        if ($mode === 'web' && strpos($_SERVER['REQUEST_URI'], '/media/') !== 0) {
            $stream->seek(-1, SEEK_END);
            // $stream->write('<script src="/media/0/sys/src/Profiler/profiler.js"></script>');
            $stream->write('<script src="/assets/profiler.js"></script>');
            $stream->rewind();
        }

        if (strpos($_SERVER['REQUEST_URI'], '/media/') !== 0 && strpos($_SERVER['REQUEST_URI'], '/_profiler/') !== 0) {
            register_shutdown_function([$this,'shutdown'], $size);
        }

        return $response;
    }

    public function shutdown(int $size)
    {
        $time = (hrtime(true) - $this->start)/1e+6;
        
        $array = $this->model->showProfiles();
        $profiler = $this->model->get($_SERVER['REQUEST_URI']);
        // dd($profiler);
        $data['queries'] = count($array);

        $duration = 0;
        $profiles = [];

        // dd($array);

        foreach ($array as $row) {
            $row = (object) $row;
            $duration += $row->Duration * 1000;
            $profiles[] = $row->Query;
        }

        $data['uri'] = $_SERVER['REQUEST_URI'];
        $data['size'] = $size;
        $data['time'] = ($profiler) ? $this->avg($time, $profiler->time, $profiler->counter) : $time;
        $data['memory'] = memory_get_usage() - $this->memory;
        $data['duration'] = ($profiler) ? $this->avg($duration, $profiler->duration, $profiler->counter) : $duration;
        $data['counter'] = ($profiler) ? ++$profiler->counter : 1;
        $data['profiles'] = json_encode($profiles);

        $this->model->set($data);
    }

    public function get($uri)
    {
        return $this->model->get($uri);
    }

    private function avg($value, $average, $count)
    {
        return (($average * $count) + $value) / ($count + 1);
    }
}
