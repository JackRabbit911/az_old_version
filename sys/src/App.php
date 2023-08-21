<?php

namespace Sys;

// use Sys\Profiler\Profiler;
use Az\Route\RouteCollectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewareResolverInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use HttpSoft\Emitter\EmitterInterface;
use Sys\DefaultHandler;
use Sys\Exception\SetErrorHandlerInterface;

final class App
{
    private ServerRequestInterface $request;
    private RouteCollectionInterface $route;
    private MiddlewarePipelineInterface $pipeline;
    private MiddlewareResolverInterface $resolver;
    private RequestHandlerInterface $defaultHandler;
    private EmitterInterface $emitter;
    private PostProcess $postProcess;

    public function __construct(
        ServerRequestInterface $request,
        RouteCollectionInterface $route,
        MiddlewarePipelineInterface $pipeline,
        MiddlewareResolverInterface $resolver,    
        EmitterInterface $emitter,
        SetErrorHandlerInterface $setErrorHandler,
        DefaultHandler $defaultHandler,
        PostProcess $postProcess,
    )
    {
        $this->request = $request;
        $this->route = $route;
        $this->pipeline = $pipeline;
        $this->resolver = $resolver;
        $this->emitter = $emitter;
        $this->defaultHandler = $defaultHandler;
        $this->postProcess = $postProcess;

        $this->setDisplayError();
        $setErrorHandler;
    }

    /**
     * Adds a middleware to the pipeline.
     *
     * Wrapper over the `MiddlewarePipelineInterface::pipe()` method.
     *
     * @param mixed $middleware any valid value for converting it to `Psr\Http\Server\MiddlewareInterface` instance.
     * @param string|null $path path prefix from the root to which the middleware is attached.
     */
    public function pipe($middleware, string $path = null): void
    {
        $this->pipeline->pipe($this->resolver->resolve($middleware), $path);
    }

     /**
     * Run the application.
     * @return ResponseInterface
     */   
    public function run(): void
    {
        $mode = getMode();

        if (is_file(($mode_pipeline = CONFIGPATH . 'pipeline/' . $mode . '.php'))) {
            require_once $mode_pipeline;
        }

        if (is_file(($common_pipeline = CONFIGPATH . 'pipeline/common.php'))) {
            require_once $common_pipeline;
        }

        if (is_file(($common_routes = CONFIGPATH . 'routes/common.php'))) {
            require_once $common_routes;
        }

        if (is_file(($mode_routes = CONFIGPATH . 'routes/' . $mode . '.php'))) {
            require_once $mode_routes;
        }

        $response = $this->pipeline->process($this->request, $this->defaultHandler);

        $response = $this->postProcess->process($response, $mode);

        $this->emitter->emit($response);
    }

    private function setDisplayError()
    {
        // dd(env());
        if (env()->env <= TESTING) {
            ini_set('display_errors', 0);
        } else {
            ini_set('display_errors', 1);
        }
    }
}
