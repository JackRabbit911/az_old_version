<?php

use Sys\Exception\ErrorController;
use Sys\Controller\Media;
use Sys\Image\ImageController;

$this->route->get('/error/{code}', ErrorController::class);
$this->route->get('/media/{lifetime}/{file}', [Media::class], 'media')
    ->tokens(['lifetime' => '\d*', 'file' => '.*']);
$this->route->get('/image/{action}/{file}', ImageController::class, 'image')
    ->tokens(['action' => '[\w\-\d]+', 'file' => '.*']);
