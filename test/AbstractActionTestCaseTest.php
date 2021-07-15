<?php

declare(strict_types=1);

namespace ZfeggTest\ExpressiveTest;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Mezzio\Application;
use Zfegg\ExpressiveTest\AbstractActionTestCase;
use Zfegg\ExpressiveTest\PassMiddleware;
use Zfegg\ExpressiveTest\TestResponse;

class AbstractActionTestCaseTest extends AbstractActionTestCase
{
    use ProphecyTrait;

    public function testLoadContainer()
    {
        $this->assertEquals($this->container, $this->loadContainer());
        $this->assertInstanceOf(ContainerInterface::class, $this->loadContainer());
    }

    public function params()
    {
        $body = '{"a":"b"}';

        return [
            'BodyString' => [$body],
            'BodyStreamInterface' => [$this->makeStream($body)],
        ];
    }

    public function makeStream($body = '{"a":"b"}')
    {
        $stream = new Stream('php://memory', 'r+');
        $stream->write($body);
        $stream->rewind();

        return $stream;
    }

    /**
     *
     * @dataProvider params
     */
    public function testAction($body)
    {
        $app = $this->container->get(Application::class);
        $app->post('/', [$this, 'postHandler']);
        $this->runApp(
            'POST',
            '/?test=1',
            ['body' => '2'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            $body,
            ['cookie' => '3']
        );
    }

    public function testInvalidBodyParamAction()
    {
        $this->expectException(\RuntimeException::class);
        $app = $this->container->get(Application::class);
        $app->post('/', [$this, 'postHandler']);
        $this->runApp(
            'POST',
            '/?test=1',
            ['body' => '2'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            ['invalid test'],
            ['cookie' => '3']
        );
    }

    public function postHandler(ServerRequestInterface $request)
    {
        $servers = $request->getServerParams();
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('{"a":"b"}', $request->getBody()->getContents());
        $this->assertEquals(['test' => '1'], $request->getQueryParams());
        $this->assertEquals(
            $expected = ['HTTP_CONTENT_TYPE' => 'application/json'],
            array_intersect_assoc($expected, $servers)
        );
        $this->assertEquals(['body' => '2'], $request->getParsedBody());
        $this->assertEquals(['cookie' => '3'], $request->getCookieParams());

        return new Response();
    }

    public function testWithHeaders()
    {
        $headers = ['test' => 'abc'];
        $this->withHeaders($headers);
        $this->assertEquals($headers, $this->defaultHeaders);

        $this->withHeader('test2', 'abc2');
        $this->assertEquals($headers + ['test2' => 'abc2'], $this->defaultHeaders);

        $this->flushHeaders();
        $this->assertEquals([], $this->defaultHeaders);
    }

    public function testWithServerVariables()
    {
        $servers = ['test' => 'abc'];
        $this->withServerVariables($servers);

        $this->assertEquals($servers, $this->serverVariables);
    }

    public function testWithoutMiddleware()
    {
        $this->container->setService('test', ['test']);
        $this->withoutMiddleware('test');
        $obj = $this->container->get('test');
        $this->assertInstanceOf(PassMiddleware::class, $obj);
    }

    public function testWithCookies()
    {
        $cookies = ['test' => 'abc'];
        $this->withCookies($cookies);
        $this->assertEquals($cookies, $this->defaultCookies);

        $this->withCookie('test2', 'abc2');
        $this->assertEquals($cookies + ['test2' => 'abc2'], $this->defaultCookies);
    }

    public function testFrom()
    {
        $referer = 'http://localhost';
        $this->from($referer);

        $this->assertEquals(['referer' => $referer], $this->defaultHeaders);
    }

    public function methods()
    {
        return [
            'options' => ['options'],
            'get' => ['get'],
            'delete' => ['delete'],
            'post' => ['post', ['test' => '123']],
            'put' => ['put', ['test' => '123']],
            'patch' => ['patch', ['test' => '123']],
        ];
    }

    public function jsonMethods()
    {
        return [
            'getJson' => ['getJson'],
            'postJson' => ['postJson', ['test' => '123']],
            'putJson' => ['putJson', ['test' => '123']],
            'patchJson' => ['patchJson', ['test' => '123']],
        ];
    }

    /**
     * @dataProvider methods
     */
    public function testMethods($method, ...$args)
    {
        /** @var Application $app */
        $app = $this->container->get(Application::class);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200)->shouldBeCalled();

        $app->route('/', function (ServerRequestInterface $request) use ($response, $args) {
            if (isset($args[1])) {
                $this->assertEquals(json_encode($args[1]), (string)$request->getBody());
            }
            return $response->reveal();
        }, [strtoupper(str_replace('Json', '', $method))]);

        $this->$method('/', ...$args)->assertOk();
    }

    /**
     * @dataProvider jsonMethods
     */
    public function testJsonMethods($callMethod, ...$args)
    {
        $method = strtoupper(str_replace('Json', '', $callMethod));

        /** @var Application $app */
        $app = $this->container->get(Application::class);
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200)->shouldBeCalled();
        $response->getBody()->willReturn($this->makeStream('{"a":123}'))->shouldBeCalled();

        $app->route('/', function (ServerRequestInterface $request) use ($response, $args) {
            if (isset($args[1])) {
                $this->assertEquals(json_encode($args[1]), (string)$request->getBody());
            }
            return $response->reveal();
        }, [$method]);

        /** @var TestResponse $response */
        $response = $this->$callMethod('/', ...$args);
        $response->assertOk()
                ->assertJson(['a' => 123]);
    }

    public function testCallGetParameters()
    {
        /** @var Application $app */
        $app = $this->container->get(Application::class);
        $app->get('/', function (ServerRequestInterface $request) {
            $this->assertEquals(['test' => '123'], $request->getQueryParams());
            return new Response\EmptyResponse();
        });
        $app->get('/test2', function (ServerRequestInterface $request) {
            $this->assertEquals(['test' => '123', 'key2' => 'val2'], $request->getQueryParams());
            return new Response\EmptyResponse();
        });

        $response = $this->call('GET', '/', ['test' => '123']);
        $response->assertNoContent();

        $response = $this->call('GET', '/test2?key2=val2', ['test' => '123']);
        $response->assertNoContent();
    }
}
