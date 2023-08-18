<?php

namespace Az\Route;

use Az\Route\Route;
use Az\Exception\HttpException;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;

final class RouteCollection implements RouteCollectionInterface
{
    use Special;

    private array $routes = [];
    private string $groupPrefix = '';
    private array $pipeline = [];
    private array $filters = [];
    private array $defaults = [];
    private array $tokens = [];

    public function group(string $prefix, callable $callback): self
    {
        $previousGroupPrefix = $this->groupPrefix;
        $this->groupPrefix .= '/' . ltrim($prefix, '/');
        $callback($this);
        $this->groupPrefix = $previousGroupPrefix;
        return $this;
    }

    public function pipe(...$params): self
    {
        foreach($params as $middleware) {
            if (is_array($middleware)) {
                $this->pipeline += $middleware;
            } else {
                $this->pipeline[] = [$this->groupPrefix, $middleware];
            }
        }

        return $this;
    }

    public function middleware(...$params): self
    {
        return $this->pipe($params);
    }

    public function getPipeline()
    {
        return $this->pipeline;
        // return ($prefix === null) ? $this->middlewares : $this->middlewares[$prefix] ?? [];
    }

    public function filter(callable $filter): self
    {
        $this->filters[$this->groupPrefix][] = $filter;
        return $this;
    }

    public function defaults(array $defaults): self
    {
        $this->defaults[$this->groupPrefix] = $defaults;
        return $this;
    }

    public function tokens(array $token)
    {
        $this->tokens[$this->groupPrefix] = $token;
        return $this;
    }

    public function add(string $pattern, $handler, $name = null): Route
    {
        // if ($this->has($name)) {
        //     throw new RuntimeException(sprintf('The route "%s" already exists', $name));
        // }

        $pattern = $this->groupPrefix . '/' .ltrim($pattern, '/');
        $route = new Route(rtrim($pattern, '/'), $handler, $name);
        $route->groupPrefix($this->groupPrefix);
        
        if ($name) {
            $this->routes[$name] = $route;
        } else {
            $this->routes[] = $route;
        }
        
        return $route;
    }

    public function any(string $pattern, $handler, $name = null): Route
    {
        return $this->add($pattern, $handler, $name);
    }

    public function get(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('GET', 'HEAD', 'OPTIONS');
    }

    public function post(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('POST', 'OPTIONS');
    }

    public function put(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('PUT');
    }

    public function patch(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('PATCH');
    }

    public function delete(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('DELETE');
    }

    public function head(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('HEAD');
    }

    public function options(string $pattern, $handler, ?string $name = null): Route
    {
        return $this->add($pattern, $handler, $name)->methods('OPTIONS');
    }

    public function getRoute(string $name): Route
    {
        return $this->routes[$name];
    }

    public function getAll(): array
    {
        return $this->routes;
    }

    public function has($name): bool
    {
        return isset($this->routes[$name]);
    }

    public function addRoute(Route $route): void
    {
        $name = $route->getName();

        if ($this->has($name)) {
            throw new RuntimeException(sprintf('The route "%s" already exists', $name));
        }

        $this->routes[$name] = $route;
    }

    public function remove(string $name): Route
    {
        if (!$this->has($name)) {
            throw new RuntimeException(sprintf('The route "%s" not found', $name));
        }

        $removed = $this->routes[$name];
        unset($this->routes[$name]);

        return $removed;
    }

    public function clear(): void
    {
        $this->routes = [];
    }

    public function match(ServerRequestInterface $request)
    {
        foreach ($this->routes as $route) {
            $groupPrefix = $route->getGroupPrefix();
            $tokens = $this->arrayMerge($route->getTokens(), $this->tokens, $groupPrefix);
            $route->tokens($tokens);

            foreach ($this->filters as $key => $filters) {
                if (strpos($groupPrefix, $key) === 0) {
                    foreach ($filters as $filter) {
                        $route->filter($filter);
                    }
                }
            }

            $defaults = $this->arrayMerge($route->getDefaults(), $this->defaults, $groupPrefix);
            $route->defaults($defaults);

            if ($route->match($request)) {
                return $route;
            }            
        }

        return false;
        // throw new HttpException(404, 'The requested route not match');
    }

    public function path(string $name, array $parameters = []): string
    {
        $route = $this->getRoute($name);
        return $route->path($parameters);
    }

    private function arrayMerge($array1, $array2, $substr)
    {
        foreach ($array2 as $key => $value) {
            if (strpos($substr, $key) === 0) {
                $array1 += $value;
            }
        }

        return $array1;
    }
}
