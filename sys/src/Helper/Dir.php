<?php

namespace Sys\Helper;

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use CallbackFilterIterator;
use SplFileInfo;

class Dir
{
    public function removeAll($dir)
    {
        $includes = new FilesystemIterator($dir);

        foreach ($includes as $include) {

            if(is_dir($include) && !is_link($include)) {

                $this->removeAll($include);
            }
            else {
                unlink($include);
            }
        }

        rmdir($dir);
    }

    public function removeEmpty($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $i = 0;
        foreach ($iterator as $file) {
            if ($file->isDir() && count(scandir($file->getPathname())) === 2) {
                rmdir($file->getPathname());
                $i++;
            }
        }
         
        return $i;
    }

    public function callbackIterator($dir, $callback)
    {
        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        return $callback($iterator);
    }

    public function clearByLifetime($dir, $lifetime)
    {
        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $i = 0;

        foreach ($iterator as $info) {
            if ($info->isDir()) {
                continue;
            }

            if ((time() - $info->getCTime()) > $lifetime) {
                unlink($info->getPathname());
                $i++;
            }
        }
         
        return $i;
    }
}
