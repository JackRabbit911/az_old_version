<?php

use App\Http\Controller\Home;
use Sys\Welcome\Http\Controller\Welcome;

$this->route->controller('{action?}', Home::class, 'home');
$this->route->controller('/~welcome/{action?}', Welcome::class);
