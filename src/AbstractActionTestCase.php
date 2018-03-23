<?php

namespace Zfegg\ExpressiveTest;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\Middleware\ErrorHandler;

abstract class AbstractActionTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function getProjectDir()
    {
        $reflector = new ReflectionClass(ClassLoader::class);
        $file = $reflector->getFileName();
        return realpath(dirname($file) . '/../../');
    }

    public function loadContainer()
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

    public function setUp()
    {
        $this->container = $this->loadContainer();
        $this->container->setAllowOverride(true);
        $this->container->setService(ErrorHandler::class, new PassMiddleware());
        $this->container->addDelegator(
            Application::class,
            function ($container, $name, callable $callback) {
                $app = $callback();
                $factory = $container->get(MiddlewareFactory::class);
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
        $method,
        $requestUri,
        array $parsedBody = [],
        $servers = [],
        $body = null,
        array $cookies = [],
        array $files = []
    ) {
        $servers['REQUEST_METHOD'] = $method;
        $servers['REQUEST_URI'] = $requestUri;

        $request = self::mockRequest(
            $servers,
            $parsedBody,
            $body,
            $cookies,
            $files
        );

        /** @var \Zend\Expressive\Application $app */
        $app = $this->container->get(Application::class);

        return $app->handle($request);
    }

    public static function mockRequest(
        $servers = [],
        array $parsedBody = null,
        $body = null,
        array $cookies = [],
        array $files = []
    ) {
        $_SERVER = array_merge(
            [
                'SERVER_PROTOCOL'    => 'HTTP/1.1',
                'REQUEST_METHOD'     => 'GET',
                'SCRIPT_NAME'        => '',
                'REQUEST_URI'        => '',
                'QUERY_STRING'       => '',
                'SERVER_NAME'        => 'localhost',
                'SERVER_PORT'        => 80,
                'HTTP_HOST'          => 'localhost',
                'HTTP_ACCEPT'        => '*/*',
                'HTTP_USER_AGENT'    => 'Test',
                'HTTP_CONTENT_TYPE'  => 'application/x-www-form-urlencoded',
                'REMOTE_ADDR'        => '127.0.0.1',
                'REQUEST_TIME'       => time(),
                'REQUEST_TIME_FLOAT' => microtime(true),
                'HTTP_ORIGIN'        => 'http://localhost',
            ],
            $servers
        );

        $urlData = parse_url($_SERVER['REQUEST_URI']);

        $_POST = $_GET = [];

        if (! empty($urlData['query'])) {
            parse_str($urlData['query'], $_GET);
        }

        $_POST = $parsedBody;

        $server = ServerRequestFactory::normalizeServer($_SERVER);
        $files = ServerRequestFactory::normalizeFiles($files);
        $headers = ServerRequestFactory::marshalHeaders($server);

        if ($body instanceof StreamInterface) {
            $stream = $body;
        } else {
            $stream = new Stream('php://memory', 'r+');
            if (is_string($body)) {
                $stream->write($body);
            } elseif (! is_null($body)) {
                throw new \RuntimeException('Invalid body type');
            }
        }

        $request = new ServerRequest(
            $server,
            $files,
            ServerRequestFactory::marshalUriFromServer($server, $headers),
            ServerRequestFactory::get('REQUEST_METHOD', $server, 'GET'),
            $stream,
            $headers,
            $cookies,
            $_GET,
            $parsedBody,
            explode('/', $server['SERVER_PROTOCOL'])[1]
        );

        return $request;
    }
}
