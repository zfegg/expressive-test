<?php

declare(strict_types=1);

namespace Zfegg\ExpressiveTest;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;

use function Zend\Diactoros\marshalHeadersFromSapi;
use function Zend\Diactoros\marshalUriFromSapi;
use function Zend\Diactoros\normalizeServer;
use function Zend\Diactoros\normalizeUploadedFiles;

class MockRequestFactory
{
    public static $defaultServerParams = [
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
        'HTTP_ORIGIN'        => 'http://localhost',
    ];

    /**
     * Create request
     * @param array $servers
     * @param array|null $parsedBody
     * @param string|\Psr\Http\Message\StreamInterface $body
     * @param array $cookies
     * @param array $files
     * @return ServerRequest
     */
    public static function create(
        $servers = [],
        array $parsedBody = null,
        $body = null,
        array $cookies = [],
        array $files = []
    ): ServerRequest {

        $_SERVER = array_merge(
            self::$defaultServerParams,
            [
                'REQUEST_TIME'       => time(),
                'REQUEST_TIME_FLOAT' => microtime(true),
            ],
            $servers
        );

        $urlData = parse_url($_SERVER['REQUEST_URI']);

        $_POST = $_GET = [];

        if (! empty($urlData['query'])) {
            parse_str($urlData['query'], $_GET);
        }

        $_POST = $parsedBody;

        if (function_exists('\Zend\Diactoros\normalizeServer')) { // Diactoros v2.0
            $server = normalizeServer($_SERVER);
            $files   = normalizeUploadedFiles($files);
            $headers = marshalHeadersFromSapi($_SERVER);
            $uri = marshalUriFromSapi($_SERVER, $headers);
        } else { // Diactoros v1.7
            $server  = ServerRequestFactory::normalizeServer($_SERVER);
            $files   = ServerRequestFactory::normalizeFiles($files);
            $headers = ServerRequestFactory::marshalHeaders($server);
            $uri = ServerRequestFactory::marshalUriFromServer($server, $headers);
        }

        if ($body instanceof StreamInterface) {
            $stream = $body;
        } else {
            $stream = new Stream('php://memory', 'r+');
            if (is_string($body)) {
                $stream->write($body);
                $stream->rewind();
            } elseif (! is_null($body)) {
                throw new RuntimeException('Invalid body type');
            }
        }

        $request = new ServerRequest(
            $server,
            $files,
            $uri,
            $server['REQUEST_METHOD'] ?? 'GET',
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
