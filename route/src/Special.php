<?php

namespace Az\Route;

trait Special
{
    public function controller($pattern, $controller, $name = null)
    {
        if ($name === true) {
            $name = strtolower((new \ReflectionClass($controller))->getShortName());
        }
        
        global $container;
        $handler = function ($request) use ($container, $controller) {
            $route = $request->getAttribute(Route::class);
            $params = $route->getParameters();
            $action = $params['action'] ?? '__invoke';
            return (new Invoker($container))->invoke($request, [$controller, $action], $params);
        };

        return $this->add($pattern, $handler, $name)
        ->defaults(['action' => '__invoke'])
        ->tokens(['action' => '[\w]*',])
        ->filter(function ($route) use ($container, $controller) {
            $params = $route->getParameters();
            $action = $params['action'] ?? '__invoke';
            return is_callable([$container->get($controller), $action]);
        });
    }

    public function default(string $pattern = '{controller?}/{action?}/{id?}/{param?}/{slug?}')
    {
        global $container;

        $handler = function ($request) use ($container) {
            $route = $request->getAttribute(Route::class);
            $params = $route->getParameters();
            $controller = 'App\Http\Controller\\' . ucfirst($params['controller']);
            $action = $params['action'] ?? '__invoke';
            return (new Invoker($container))->invoke($request, [$controller, $action], $params);
        };
        
        return $this->add($pattern, $handler, 'default')
            ->defaults([
                'controller' => 'index',
                'action' => '__invoke',
            ])
            ->tokens([
                'controller' => '[\w]*',
                'action' => '[\w]*',
                'id' => '[\d]*',
                'param' => '[\w]*',
                'slug' => '[\w\-]*',
                ])
            ->filter(function ($route) use ($container) {
                $params = $route->getParameters();
                $controller = 'App\Http\Controller\\' . ucfirst($params['controller']);
                $action = $params['action'] ?? '__invoke';

                if (!$container->has($controller)) {
                    return false;
                }
                
                return is_callable([$container->get($controller), $action]);
            });
    }
}
