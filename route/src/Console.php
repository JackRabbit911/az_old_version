<?php

namespace Az\Route;

use Az\Console\AbstractConsole;
// use Az\I18n\Setting;
use Az\Route\RouteCollectionInterface;
use Psr\Container\ContainerInterface;

class Console extends AbstractConsole
{
    // private Setting $i18n;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        // $this->i18n = $container->get(Setting::class);
    }

    public function web()
    {
        $this->route = new RouteCollection();
        require_once CONFIGPATH . 'routes/web.php';
        $routes = $this->route->getAll();

        return $this->run($routes, __FUNCTION__);
    }

    public function api()
    {
        $this->route = new RouteCollection();
        require_once CONFIGPATH . 'routes/api.php';
        $routes = $this->route->getAll();

        return $this->run($routes, __FUNCTION__);
    }

    public function cli()
    {
        $route = $this->container->get(RouteCollectionInterface::class);
        $routes = $route->getAll();

        return $this->run($routes, __FUNCTION__);
    }

    public function all()
    {
        $this->route = $this->container->get(RouteCollectionInterface::class);
        // $this->route = new RouteCollection();
        // print_r($this->route->getAll()); exit;
        // $this->route->clear();
        require_once CONFIGPATH . 'routes/cli.php';
        require_once CONFIGPATH . 'routes/api.php';
        require_once CONFIGPATH . 'routes/web.php';
        $routes = $this->route->getAll();

        return $this->run($routes, __FUNCTION__);
    }

    public function help()
    {
        return ['out' => 'Usage: cli routes
        call with parameter: all, cli, web or api'];
    }

    private function run($routes, $mode)
    {
        $i = 1;
        foreach ($routes as $k => $route) {
            $handler = $route->getHandler();

            if (is_array($handler)) {
                if (is_object($handler[0])) {
                    $handler[0] = get_class($handler[0]);
                }

                if (!isset($handler[1])) {
                    $handler[1] = '';
                }

                $handler = '[' . $handler[0] . ', ' . $handler[1] . ']';
            } elseif (is_object($handler)) {
                $handler = get_class($handler);
            }

            $output[] = [
                '#'         => $i++,
                'Name'      => $route->getName(),
                'Methods'   => implode(', ', $route->getMethods()),
                'Pattern'   => $route->getPattern(),
                'Handler'   => $handler,
            ];
        }

        return (isset($output)) ? ['table'  => $output] : ['out' => "$mode routes not found"];
    }
}
