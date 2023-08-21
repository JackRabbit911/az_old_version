<?php

namespace Sys\Image;

use Sys\Controller\BaseController;
use Sys\Options;
use Sys\FileResponse;

final class ImageController extends BaseController
{
    use Options;

    private Im $im;
    private Cache $cache;
    private bool $is_cache = true;
    private int $cacheLifetime;
    private $dir = 'app/storage/';
    private $bg;
    private $pct;

    public function __construct(Cache $cache)
    {
        $this->options(CONFIGPATH . 'images.php');
        $this->cache = $cache;
    }

    public function __invoke($action, $file)
    {
        if (!($fn = $this->cache->get($action, $file))) {
            $fn = $this->dir . $file;
        } else {
            return new FileResponse($fn, $this->cacheLifetime);
        }

        $this->im = new Im($fn);
        $arr_action = explode('-',$action);
        $action = $arr_action[0];
        $size = $arr_action[1] ?? null;
        
        return $this->$action($size);
    }

    private function original()
    {
        $this->im->watermark();
        return $this->response();
    }

    private function thumbnail($size)
    {
        $size = explode('x', $size);
        $width = $size[0];
        $height = $size[1] ?? $width;

        $this->im->insert($width, $height, $this->bg, $this->pct);

        return $this->response();
    }

    private function width($size)
    {
        $this->im->resize($size, null);
        return $this->response();
    }

    private function height($size)
    {
        $this->im->resize(null, $size);
        return $this->response();
    }

    private function square($size)
    {
        $this->im->crop()->resize($size, $size);
        return $this->response();
    }

    private function response()
    {
        if ($this->is_cache && !$this->im->isFallback()) {            
            $file = $this->cache->mkDir()->file();

            if(!is_file($file)) {
                $this->im->save($file);
            }

            return new FileResponse($file, $this->cacheLifetime);
        } else {
            return $this->im->out();
        }
    }
}
