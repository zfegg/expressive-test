<?php

declare(strict_types=1);

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

$aggregator = new ConfigAggregator([
    \Zend\Expressive\Router\FastRouteRouter\ConfigProvider::class,
    \Zend\Expressive\ConfigProvider::class,
    \Zend\Expressive\Router\ConfigProvider::class,
]);

$config = $aggregator->getMergedConfig();

// Build container
$container = new ServiceManager();
(new Config($config['dependencies']))->configureServiceManager($container);

// Inject config
$container->setService('config', $config);

return $container;
