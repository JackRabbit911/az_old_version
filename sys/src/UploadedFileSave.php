<?php

namespace Sys;

use Psr\Http\Message\UploadedFileInterface;

final class UploadedFileSave
{
    private $path = 'app/storage/uploads/';
    private $file;
    private int $user_id;

    public function __construct(UploadedFileInterface|array $file, int $user_id)
    {
        $this->file = $file;
        $this->user_id = $user_id;
    }

    public function save(callable|string $arg = null): string|array
    {
        if (is_callable($arg)) {
            if (is_array($this->file)) {
                foreach ($this->file as $file) {
                    $result[] = call_user_func($arg, $file, $this->user_id);
                }
            } else {
                $result = call_user_func($arg, $this->file, $this->user_id);
            }
        } else {
            $result = $this->saveDefault($arg);
        }

        return $result;
    }

    private function saveDefault($path): string|array
    {
        if (empty($path)) {
            $path = $this->path . $this->user_id . '/';
        }

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_writable($path)) {
            chmod($path, 0777);
        }

        if (is_array($this->file)) {
            foreach ($this->file as $file) {
                $result[] = $this->saveByString($file, $path);
            }
        } else {
            // dd($this->file, $path);
            $result = $this->saveByString($this->file, $path);
        }

        return $result;
    }

    private function saveByString($file, $path): string
    {
        $filename = $this->santizeFilename($file->getClientFilename());
        $filepath = $path . $filename;
        $file->moveTo($filepath);
        return $filepath;
    }

    private function santizeFilename($str): string
    {
        return preg_replace_callback_array([
            '/\s+/' => function($matches) {return '_';},
            '/\b[A-ZА-ЯЁ]+\b$/' => function($matches) {return strtolower($matches[0]);}
        ], $str);
    }
}
