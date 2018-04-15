<?php

namespace ZfeggTest\ExpressiveTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use Zend\Expressive\Application;
use Zfegg\ExpressiveTest\AbstractActionTestCase;

class AbstractActionTestCaseTest extends AbstractActionTestCase
{
    public function params()
    {

        $body = '{"a":"b"}';
        $stream = new Stream('php://memory', 'r+');
        $stream->write($body);
        $stream->rewind();

        return [
            'BodyString' => [$body],
            'BodyStreamInterface' => [$stream],
        ];
    }

    /**
     *
     * @dataProvider params
     */
    public function testAction($body)
    {
        $app = $this->container->get(Application::class);
        $app->post('/', [$this, 'postHandler']);
        $response = $this->runApp(
            'POST',
            '/?test=1',
            ['body' => '2'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            $body,
            ['cookie' => '3']
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function postHandler(ServerRequestInterface $request)
    {
        $servers = $request->getServerParams();
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('{"a":"b"}', $request->getBody()->getContents());
        $this->assertArraySubset(['test' => '1'], $request->getQueryParams());
        $this->assertArraySubset(['HTTP_CONTENT_TYPE' => 'application/json'], $servers);
        $this->assertArraySubset(['body' => '2'], $request->getParsedBody());
        $this->assertArraySubset(['cookie' => '3'], $request->getCookieParams());

        return new Response();
    }
}
