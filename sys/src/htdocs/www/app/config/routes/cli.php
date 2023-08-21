<?php

use App\Http\Controller\TestCli;
use Az\Route\Route;
use Az\Session\Session;
use HttpSoft\Response\TextResponse;
use Sys\Migrations\Controller;

$this->route->group('console', function ($route) {
    $route->get('/session/gc', function () {
        global $container;
        $session = $container->get(Session::class);
        // $session->start();
        $count = $session->gc();
        $session->destroy();
        return new TextResponse($count);
    });

    $route->controller('migrate/{action}/{path?}', Controller::class)
        ->methods('get');

    $route->controller('test', TestCli::class);
});
