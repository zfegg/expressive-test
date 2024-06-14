<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;

$aggregator = new ConfigAggregator([
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
]);

$config = $aggregator->getMergedConfig();

// Build container
$container = new ServiceManager();
$container->configure($config['dependencies']);

// Inject config
$container->setService('config', $config);

return $container;
