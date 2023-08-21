<?php

namespace Sys\Image;

use GdImage;
use Exception;

class Im
{
    public ?GdImage $image = null;
    private string $outfunc = 'imagejpeg';
    private string $fallback = 'app/storage/img/dist/no-image.jpg';
    private string $watermark = 'app/storage/img/dist/watermark.png';
    private string $mime;
    private string $file;

    private $width;
    private $height;
    private $ratio;

    public function __construct(string $file)
    {
        try {
            $info = getimagesize($file);
        } catch(Exception $e) {
            $file = $this->fallback;
            $info = getimagesize($file);
        }
        
        if ($info === false) {
            $file = $this->fallback;
            $info = getimagesize($file);
        }

        $this->file = $file;

        list($this->width, $this->height, $type) = $info;
        $this->mime = $info['mime'] ?? null;

        if ($info) {
            $this->ratio = $this->width/$this->height;
        }

        switch ($type) {
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($file);
                $this->outfunc = 'imagegif';
                $this->mime = 'image/gif';
                break;
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($file);
                $this->outfunc = 'imagejpeg';
                $this->mime = 'image/jpeg';
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($file);
                $this->outfunc = 'imagepng';
                $this->mime = 'image/png';
                break;
            case IMAGETYPE_BMP:
                $this->image = imagecreatefrombmp($file);
                $this->outfunc = 'imagebmp';
                $this->mime = 'image/bmp';
                break;
            case IMAGETYPE_WBMP:
                $this->image = imagecreatefromwbmp($file);
                $this->outfunc = 'imagewbmp';
                $this->mime = 'image/wbmp';
                break;
            case IMAGETYPE_WEBP:
                $this->image = imagecreatefromwebp($file);
                $this->outfunc = 'imagewebp';
                $this->mime = 'image/webp';              
                break;
            // default:
        }
    }

    public function resize($width = null, $height = null)
    {
        if (!($width || $height) || ($width >= $this->width || $height >= $this->height)) {
            return $this;
        }

        if ($width === null) {
            $width = round($height * $this->ratio);
        }

        if ($height === null) {
            $height = round($width/$this->ratio);
        }

        $dest = imagecreate($width, $height);

        imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->image = $dest;
        imagedestroy($dest);

        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function insert($width, $height, $bg = null, $pct = null)
    {
        $dest = imagecreate($width, $height);

        if (is_string($bg)) {
            $bg = $this->hex2rgb($bg);
        }

        if (!empty($bg) && (is_string($bg) || is_array($bg))) {
            if ($pct === null) {
                $pct = 0;
            }

            list($r, $g, $b) = $bg;
            $color = imagecolorallocatealpha($dest, $r, $g, $b, $pct);
            imagefill($dest, 0, 0, $color);
        }

        if ($bg === true) {
            if ($pct === null) {
                $pct = 100;
            }

            imagecopyresized($dest, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

            for ($x=1; $x<=3; $x++) {
                imagefilter($dest, IMG_FILTER_GAUSSIAN_BLUR);
            }

            $src = imagecreate($width, $height);
            imagecopymerge($src, $dest, 0, 0, 0, 0, $width, $height, $pct);

            $dest = $src;
            imagedestroy($src);
        }

        $ratio = $width/$height;

        if ($ratio < $this->ratio) {
            $w = $width;
            $h = round($width / $this->ratio);
            $x = 0;
            $y = round(($height - $h) / 2);
        } else {
            $w = round($height * $this->ratio);
            $h = $height;
            $x = round(($width - $w) / 2);
            $y = 0;
        }

        imagecopyresampled($dest, $this->image, $x, $y, 0, 0, $w, $h, $this->width, $this->height);
        $this->image = $dest;
        $this->width = $width;
        $this->height = $height;

        imagedestroy($dest);

        return $this;
    }

    public function crop(?array $rectangle = null)
    {
        if ($rectangle === null) {
            if ($this->ratio < 1) {
                $rectangle['x'] = 0;
                $rectangle['y'] = ($this->height - $this->width) / 2;
                $rectangle['width'] = $this->width;
                $rectangle['height'] = $this->width;
            } else {
                $rectangle['x'] = ($this->width - $this->height) / 2;
                $rectangle['y'] = 0;
                $rectangle['width'] = $this->height;
                $rectangle['height'] = $this->height;
            }
        }

        $this->width = $rectangle['width'];
        $this->height = $rectangle['height'];
        $this->ratio = $this->width/$this->height;
        $this->image = imagecrop($this->image, $rectangle);
       
        return $this;
    }

    public function watermark($x = 0, $y = 0, $file = null)
    {
        if (!$file) {
            $file = $this->watermark;
        }

        $src = new Im($file);

        $x = $this->width - $x - $src->width;
        $y = $this->height - $y - $src->height;

        imagecopymerge($this->image, $src->image, $x, $y, 0, 0, $src->width, $src->height, 50);
        imagedestroy($src->image);

        return $this;
    }

    public function save($path = null, $quality = -1)
    {
        if ($this->file === $this->fallback) {
            return $this;
        }

        if (!$path) {
            $path = $this->file;
        }

        call_user_func($this->outfunc, $this->image, $path, $quality);

        return $this;
    }

    public function out()
    {
        header("Content-Type: private, $this->mime");
        call_user_func($this->outfunc, $this->image);
        imagedestroy($this->image);
    }

    public function inline()
    {
        ob_start();
        call_user_func($this->outfunc, $this->image);
        $contents = ob_get_clean();
        return 'data:' . $this->mime . ';base64,' . base64_encode($contents);
    }

    public function isFallback()
    {
        return $this->file === $this->fallback;
    }

    private function hex2rgb($hex)
    {
        return (strlen($hex) === 4) 
            ? sscanf('#'.implode('',array_map('str_repeat',str_split(str_replace('#','', $hex)), [2,2,2])), "#%02x%02x%02x") 
            : sscanf($hex, "#%2x%2x%2x");
    }
}
