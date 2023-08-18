<?php

namespace Az\Route;

use Az\Route\RouteCollectionInterface;
use Az\Helper\Facade\FacadeAbstract;

final class Facade extends FacadeAbstract
{
    protected static $instance;
    protected static $class = RouteCollectionInterface::class;
}
