<?php

define('PRODUCTION', 10);
define('STAGE', 20);
define('TESTING', 30);
define('DEVELOPMENT', 40);

define('DOCROOT', __DIR__ . '/');
define('SYSPATH', '../../../');
define('APPPATH', '../');
define('CONFIGPATH', APPPATH . 'app/config/');

require_once SYSPATH . 'vendor/autoload.php';
require_once SYSPATH . 'vendor/az/sys/src/autoload.php';
require_once SYSPATH . 'vendor/az/sys/src/library.php';

$container = (new Sys\ContainerFactory())->create(new DI\ContainerBuilder());
$app = $container->get(Sys\App::class);
$app->run();
