<?php

declare(strict_types=1);

namespace Zfegg\ExpressiveTest;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\Middleware\ErrorHandler;

abstract class AbstractActionTestCase extends TestCase
{
    use Helper\MakeHttpRequestTrait;

    /**
     * @var ContainerInterface|\Zend\ServiceManager\ServiceManager
     */
    protected $container;

    protected function getProjectDir(): string
    {
        $reflector = new ReflectionClass(ClassLoader::class);
        $file      = $reflector->getFileName();
        return realpath(dirname($file) . '/../../');
    }

    public function loadContainer(): ContainerInterface
    {
        if ($this->container) {
            return $this->container;
        }

        $projectRoot = $this->getProjectDir();

        $containerFile = $projectRoot . '/config/container.php';
        if (file_exists($containerFile)) {
            $this->container = include $containerFile;

            return $this->container;
        }

        throw new \RuntimeException('Load container error.');
    }

    protected function setUp(): void
    {
        chdir($this->getProjectDir());
        $this->container = $this->loadContainer();
        $this->container->setAllowOverride(true);
        $this->container->setService(ErrorHandler::class, new PassMiddleware());
        $this->container->addDelegator(
            Application::class,
            function ($container, $name, callable $callback) {
                $app         = $callback();
                $factory     = $container->get(MiddlewareFactory::class);
                $projectRoot = $this->getProjectDir();

                foreach (['pipeline', 'routes'] as $file) {
                    $file = $projectRoot . "/config/$file.php";
                    if (file_exists($file)) {
                        (require $file)($app, $factory, $container);
                    }
                }

                return $app;
            }
        );
    }

    public function runApp(
        string $method,
        string $requestUri,
        array $parsedBody = [],
        array $servers = [],
        $body = null,
        array $cookies = [],
        array $files = []
    ): ResponseInterface {

        $servers['REQUEST_METHOD'] = $method;
        $servers['REQUEST_URI']    = $requestUri;

        $request = MockRequestFactory::create($servers, $parsedBody, $body, $cookies, $files);

        /** @var \Zend\Expressive\Application $app */
        $app = $this->container->get(Application::class);

        return $app->handle($request);
    }
}
