<?php

spl_autoload_register(function ($className) {

    $file = str_replace('\\', '/', $className) . '.php';

    if (!is_file($file)) {
        $file = APPPATH . lcfirst($file);
    }

    if (!is_file($file)) {
        $array = explode('/', $file);
        $module = array_shift($array);
        array_unshift($array, 'src');
        $file = trim($module . '/' . implode('/', $array));
    }

    if (!is_file($file)) {        
        return false;
    }

    require_once $file;
    return true;
});
