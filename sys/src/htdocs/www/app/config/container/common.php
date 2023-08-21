<?php

use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewarePipeline;
use HttpSoft\Runner\MiddlewareResolver;
use HttpSoft\Runner\MiddlewareResolverInterface;
use HttpSoft\ServerRequest\ServerRequestCreator;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Az\Route\RouteCollectionInterface;
use Az\Route\RouteCollection;
use Sys\Exception\SetErrorHandlerInterface;
use Sys\Exception\WhoopsAdapter;
use Sys\DefaultHandler;
use Sys\Exception\ExceptionResponseFactory;
use Pecee\Pixie\Connection;
use Pecee\Pixie\QueryBuilder\QueryBuilderHandler;
use Az\Session\Session;
use Az\Session\Driver;
use Sys\Profiler\Model\Mysql;
use Sys\Profiler\Model\ProfilerModelInterface;

return [
    ServerRequestInterface::class => fn() => (new ServerRequestCreator())->create(),
    DefaultHandler::class => fn(ExceptionResponseFactory $factory) => new DefaultHandler($factory),
    RouteCollectionInterface::class => fn() => new RouteCollection,
    MiddlewarePipelineInterface::class => fn() => new MiddlewarePipeline(),
    MiddlewareResolverInterface::class => fn(ContainerInterface $c) => new MiddlewareResolver($c),
    EmitterInterface::class => fn() => new SapiEmitter,
    SetErrorHandlerInterface::class => fn(ServerRequestInterface $request) => new WhoopsAdapter($request),
    QueryBuilderHandler::class => fn() => (new Connection('mysql', env()->array('connect', 'mysql')))->getQueryBuilder(),
    // SessionHandlerInterface::class => fn(QueryBuilderHandler $qb) => new Driver\Db($qb->pdo()),
    // Session::class => fn(SessionHandlerInterface $h) => new Session($h),
    Session::class => fn() => new Session(),
    ProfilerModelInterface::class => fn(ContainerInterface $c) => $c->get(Mysql::class),
];
