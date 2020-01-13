<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->pipe(\Laminas\Stratigility\Middleware\ErrorHandler::class);
    $app->pipe(\Mezzio\Router\Middleware\RouteMiddleware::class);
    $app->pipe(\Mezzio\Router\Middleware\DispatchMiddleware::class);
    $app->pipe(\Mezzio\Handler\NotFoundHandler::class);
};
