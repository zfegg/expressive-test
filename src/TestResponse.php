<?php

declare(strict_types=1);

namespace Zfegg\ExpressiveTest;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin ResponseInterface
 */
class TestResponse
{

    /**
     * The response to delegate to.
     *
     * @var ResponseInterface
     */
    public $baseResponse;

    /**
     * The streamed content of the response.
     *
     * @var string
     */
    protected $streamedContent;

    /**
     * Create a new test response instance.
     *
     * @param  ResponseInterface  $response
     * @return void
     */
    public function __construct(ResponseInterface $response)
    {
        $this->baseResponse = $response;
    }

    /**
     * Create a new TestResponse from another response.
     *
     * @param  ResponseInterface  $response
     * @return static
     */
    public static function fromBaseResponse(ResponseInterface $response)
    {
        return new static($response);
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        Assert::assertTrue(
            $this->getStatusCode() >= 200 && $this->getStatusCode() < 300,
            'Response status code [' . $this->getStatusCode() . '] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a 200 status code.
     *
     * @return $this
     */
    public function assertOk()
    {
        Assert::assertEquals(
            200,
            $this->getStatusCode(),
            'Response status code [' . $this->getStatusCode() . '] does not match expected 200 status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a 201 status code.
     *
     * @return $this
     */
    public function assertCreated()
    {
        $actual = $this->getStatusCode();

        Assert::assertTrue(
            201 === $actual,
            'Response status code [' . $actual . '] does not match expected 201 status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code and no content.
     *
     * @param  int  $status
     * @return $this
     */
    public function assertNoContent($status = 204)
    {
        $this->assertStatus($status);

        Assert::assertEmpty((string)$this->getBody(), 'Response content is not empty.');

        return $this;
    }

    /**
     * Assert that the response has a not found status code.
     *
     * @return $this
     */
    public function assertNotFound()
    {
        Assert::assertEquals(
            404,
            $this->getStatusCode(),
            'Response status code [' . $this->getStatusCode() . '] is not a not found status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a forbidden status code.
     *
     * @return $this
     */
    public function assertForbidden()
    {
        Assert::assertTrue(
            $this->getStatusCode() == 403,
            'Response status code [' . $this->getStatusCode() . '] is not a forbidden status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has an unauthorized status code.
     *
     * @return $this
     */
    public function assertUnauthorized()
    {
        $actual = $this->getStatusCode();

        Assert::assertTrue(
            401 === $actual,
            'Response status code [' . $actual . '] is not an unauthorized status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param  int  $status
     * @return $this
     */
    public function assertStatus($status)
    {
        $actual = $this->getStatusCode();

        Assert::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param  string|null  $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {
        Assert::assertTrue(
            $this->getStatusCode() == 301 || $this->getStatusCode() == 302,
            'Response status code [' . $this->getStatusCode() . '] is not a redirect status code.'
        );

        if (! is_null($uri)) {
            $this->assertLocation($uri);
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param  string  $headerName
     * @param  mixed  $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {
        Assert::assertTrue(
            $this->hasHeader($headerName),
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->getHeaderLine($headerName);

        if (! is_null($value)) {
            Assert::assertEquals(
                $value,
                $actual,
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response does not contains the given header.
     *
     * @param  string  $headerName
     * @return $this
     */
    public function assertHeaderMissing($headerName)
    {
        Assert::assertFalse(
            $this->hasHeader($headerName),
            "Unexpected header [{$headerName}] is present on response."
        );

        return $this;
    }

    /**
     * Assert that the current location header matches the given URI.
     *
     * @param  string  $uri
     * @return $this
     */
    public function assertLocation($uri)
    {
        Assert::assertEquals(
            $uri,
            $this->getHeaderLine('Location')
        );

        return $this;
    }


    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string  $cookieName
     * @param  mixed  $value
     * @return $this
     */
    public function assertCookie($cookieName, $value = null)
    {
        Assert::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        if (! $cookie || is_null($value)) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        Assert::assertEquals(
            $value,
            $cookieValue,
            "Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is expired.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieExpired($cookieName)
    {
        Assert::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        Assert::assertLessThan(
            time(),
            $cookie->getExpires(),
            "Cookie [{$cookieName}] is not expired, it expires at [{$cookie->getExpires()}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is not expired.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieNotExpired($cookieName)
    {
        Assert::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        Assert::assertGreaterThan(
            time(),
            $cookie->getExpires(),
            "Cookie [{$cookieName}] is expired, it expired at [{$cookie->getExpires()}]."
        );

        return $this;
    }

    /**
     * Asserts that the response does not contains the given cookie.
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieMissing($cookieName)
    {
        Assert::assertNull(
            $this->getCookie($cookieName),
            "Cookie [{$cookieName}] is present on response."
        );

        return $this;
    }

    /**
     * Get the given cookie from the response.
     *
     * @param  string $cookieName
     *
     * @return SetCookie|null
     */
    public function getCookie($cookieName): ?SetCookie
    {
        return SetCookies::fromResponse($this->baseResponse)->get($cookieName);
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertSee($value)
    {
        Assert::assertStringContainsString((string) $value, (string)$this->getBody());

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertSeeText($value)
    {
        Assert::assertStringContainsString((string) $value, strip_tags((string)$this->getBody()));

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertDontSee($value)
    {
        Assert::assertStringNotContainsString((string) $value, (string)$this->getBody());

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response text.
     *
     * @param  string  $value
     * @return $this
     */
    public function assertDontSeeText($value)
    {
        Assert::assertStringNotContainsString((string) $value, strip_tags((string)$this->getBody()));

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param  array  $data
     * @param  bool  $strict
     * @return $this
     */
    public function assertJson(array $data, bool $strict = true)
    {
        Assert::{$strict ? 'assertSame' : 'assertEquals'}(
            $data,
            array_intersect_key($this->json(), $data),
            $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @param  array  $data
     * @return string
     */
    protected function assertJsonMessage(array $data)
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: ' . PHP_EOL . PHP_EOL .
            "[{$expected}]" . PHP_EOL . PHP_EOL .
            'within response JSON:' . PHP_EOL . PHP_EOL .
            "[{$actual}]." . PHP_EOL . PHP_EOL;
    }

    /**
     * Assert that the expected value exists at the given path in the response.
     *
     * @param  string  $path
     * @param  mixed  $expect
     * @param  bool  $strict
     * @return $this
     */
    public function assertJsonPath(string $path, $expect, $strict = false)
    {
        if ($strict) {
            Assert::assertSame($expect, $this->json($path));
        } else {
            Assert::assertEquals($expect, $this->json($path));
        }

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        Assert::assertEquals($data, (array) $this->json());

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param  array  $data
     * @param  bool   $exact
     * @return $this
     */
    public function assertJsonMissing(array $data, $exact = false)
    {
        if ($exact) {
            return $this->assertJsonMissingExact($data);
        }

        $actual = (string)$this->getBody();

        foreach ($data as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            foreach ($unexpected as $value2) {
                Assert::assertStringNotContainsString(
                    $value2,
                    $actual,
                    'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
                    '[' . json_encode([$key => $value]) . ']' . PHP_EOL . PHP_EOL .
                    'within' . PHP_EOL . PHP_EOL .
                    "[{$actual}]."
                );
            }
        }

        return $this;
    }


    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJsonMissingExact(array $data)
    {
        $actual = (string)$this->getBody();

        foreach ($data as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            $rs = array_filter($unexpected, function ($val) use ($actual) {
                return strpos($actual, $val) !== false;
            });
            if (count($rs) === 0) {
                Assert::assertEquals(0, count($rs));
                return $this;
            }
        }

        Assert::fail(
            'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
            '[' . json_encode($data) . ']' . PHP_EOL . PHP_EOL .
            'within' . PHP_EOL . PHP_EOL .
            "[{$actual}]."
        );
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param  string  $key
     * @param  string  $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value)
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);

        return [
            $needle . ']',
            $needle . '}',
            $needle . ',',
        ];
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        if (is_null($structure)) {
            return $this->assertExactJson($this->json());
        }

        if (is_null($responseData)) {
            $responseData = $this->json();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                Assert::assertIsArray($responseData);

                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                Assert::assertArrayHasKey($key, $responseData);

                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                Assert::assertArrayHasKey($value, $responseData);
            }
        }

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     *
     * @param  int  $count
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonCount(int $count, $key = null)
    {
        if ($key) {
            Assert::assertCount(
                $count,
                self::arrayGet($this->json(), $key),
                "Failed to assert that the response count matched the expected {$count}"
            );

            return $this;
        }

        Assert::assertCount(
            $count,
            $this->json(),
            "Failed to assert that the response count matched the expected {$count}"
        );

        return $this;
    }


    /**
     * Validate and return the decoded response JSON.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function json($key = null)
    {
        $decodedResponse = json_decode((string)$this->getBody(), true);

        if (is_null($decodedResponse) || $decodedResponse === false) {
            Assert::fail('Invalid JSON was returned from the route.');
        }

        return $key ? self::arrayGet($decodedResponse, $key) : $decodedResponse;
    }


    /**
     * Handle dynamic calls into macros or pass missing methods to the base response.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->baseResponse->{$method}(...$args);
    }

    private static function arrayGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                $result = [];

                foreach ($target as $item) {
                    $result[] = self::arrayGet($item, $key);
                }

                return in_array('*', $key) ? array_merge([], ...$result) : $result;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }
}
