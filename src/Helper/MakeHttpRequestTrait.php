<?php

declare(strict_types=1);

namespace Zfegg\ExpressiveTest\Helper;

use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ResponseInterface;
use Zfegg\ExpressiveTest\PassMiddleware;
use Zfegg\ExpressiveTest\TestResponse;

/**
 * Trait MakeHttpRequestTrait
 *
 * @property ServiceManager $container
 */
trait MakeHttpRequestTrait
{

    /**
     * Additional headers for the request.
     *
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * Additional cookies for the request.
     *
     * @var array
     */
    protected $defaultCookies = [];

    /**
     * Additional server variables for the request.
     *
     * @var array
     */
    protected $serverVariables = [];


    /**
     * Define additional headers to be sent with the request.
     *
     * @param  array $headers
     *
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    /**
     * Add a header to be sent with the request.
     *
     * @param  string $name
     * @param  string $value
     *
     * @return $this
     */
    public function withHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    /**
     * Flush all the configured headers.
     *
     * @return $this
     */
    public function flushHeaders(): self
    {
        $this->defaultHeaders = [];

        return $this;
    }

    /**
     * Define a set of server variables to be sent with the requests.
     *
     * @param  array $server
     *
     * @return $this
     */
    public function withServerVariables(array $server): self
    {
        $this->serverVariables = $server;

        return $this;
    }

    /**
     * Disable middleware for the test.
     *
     * @param  string|array $middleware
     *
     * @return $this
     */
    public function withoutMiddleware($middleware): self
    {
        foreach ((array)$middleware as $abstract) {
            $this->container->setService($abstract, new PassMiddleware());
        }

        return $this;
    }

    /**
     * Define additional cookies to be sent with the request.
     *
     * @param  array $cookies
     *
     * @return $this
     */
    public function withCookies(array $cookies): self
    {
        $this->defaultCookies = array_merge($this->defaultCookies, $cookies);

        return $this;
    }

    /**
     * Add a cookie to be sent with the request.
     *
     * @param  string $name
     * @param  string $value
     *
     * @return $this
     */
    public function withCookie(string $name, string $value): self
    {
        $this->defaultCookies[$name] = $value;

        return $this;
    }


    /**
     * Set the referer header and previous URL session value in order to simulate a previous request.
     *
     * @param  string $url
     *
     * @return $this
     */
    public function from(string $url): self
    {
        return $this->withHeader('referer', $url);
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param  string $uri
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function get(string $uri, array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('GET', $uri, [], [], [], $server);
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param  string $uri
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function getJson(string $uri, array $headers = []): TestResponse
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('POST', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function postJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function put(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PUT', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function putJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function patch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PATCH', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function patchJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function delete(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('DELETE', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a OPTIONS request.
     *
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function options(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('OPTIONS', $uri, $data, [], [], $server);
    }

    /**
     * Call the given URI with a JSON request.
     *
     * @param  string $method
     * @param  string $uri
     * @param  array  $data
     * @param  array  $headers
     *
     * @return TestResponse
     */
    public function json(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        $content = json_encode($data);

        $headers = array_merge(
            [
                'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
                'CONTENT_TYPE'   => 'application/json',
                'Accept'         => 'application/json',
            ],
            $headers
        );

        return $this->call(
            $method,
            $uri,
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers),
            $content
        );
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|null          $content
     *
     * @return TestResponse
     */
    public function call(
        string $method,
        string $uri,
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): TestResponse {
        if (strtoupper($method) == 'GET' && $parameters) {
            if (strpos($uri, '?') !== false) {
                $uri .= '&' . http_build_query($parameters);
            } else {
                $uri .= '?' . http_build_query($parameters);
            }
            $parameters = [];
        }

        $response = $this->runApp(
            $method,
            $uri,
            $parameters,
            $server,
            $content,
            $cookies + $this->defaultCookies,
            $files
        );

        return $this->createTestResponse($response);
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param  array $headers
     *
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers): array
    {
        $servers = [];
        foreach (($this->defaultHeaders + $headers) as $name => $value) {
            $servers[$this->formatServerHeaderKey($name)] = $value;
        }

        return $servers;
    }

    /**
     * Format the header name for the server array.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function formatServerHeaderKey($name): string
    {
        if (strpos($name, 'HTTP_') !== 0 && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_' . $name;
        }

        return $name;
    }

    /**
     * Create the test response instance from the given response.
     */
    protected function createTestResponse(ResponseInterface $response): TestResponse
    {
        return TestResponse::fromBaseResponse($response);
    }
}
