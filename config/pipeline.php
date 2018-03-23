<?php


use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->pipe(\Zend\Stratigility\Middleware\ErrorHandler::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\RouteMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\DispatchMiddleware::class);
    $app->pipe(\Zend\Expressive\Handler\NotFoundHandler::class);
};
