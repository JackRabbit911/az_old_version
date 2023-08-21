<?php

namespace Sys\Image;

use Sys\Options;
use Sys\Helper\Facade\Dir;
use Psr\Http\Message\ServerRequestInterface;

class Cache
{
    use Options;

    private bool $is_cache = true;
    private $cacheDir;
    private $cacheLifetime = 60;
    private $file;
    private $dir;

    public function __construct()
    {
        $this->options(CONFIGPATH . 'images.php');
    }

    public function get($action, $file)
    {
        $info = pathinfo($file);

        $dirname = $info['dirname'];
        $basename = $info['basename'];
        $ext = $info['extension'];
        $filename = $info['filename'];

        $this->cacheDir = $this->dir . $dirname . '/cache/' . $filename;
        $cacheName = md5($action . $filename) . '.' . $ext;
        $this->file = $this->cacheDir . '/' . $cacheName;

        $this->mkDir();

        if (is_file($this->file)) {
            return $this->file;
        }

        return false;
    }

    public function mkDir()
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        return $this;
    }

    public function file()
    {
        return $this->file;
    }

    public function gc($path, $lifetime = null)
    {
        $cacheDir = $this->dir . $path . '/cache/';

        if (!$lifetime) {
            $lifetime = $this->cacheLifetime;
        }

        $i = Dir::clearByLifetime($cacheDir, $lifetime);
        Dir::removeEmpty($cacheDir);

        return $i;
    }

    public function remove($dir)
    {
        $dir = $this->dir . $dir;
        
        if (is_dir($dir)) {
            Dir::removeAll($dir);
        }        
    }

    private function expired()
    {
        if (is_file($this->path) 
            && $this->cacheLifetime 
            && (time() - filectime($this->path)) > $this->cacheLifetime) {
            unlink($this->path);
        }
    }
}
