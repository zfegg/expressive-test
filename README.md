Zend expressive handler test
============================

[![Build Status](https://travis-ci.org/zfegg/expressive-test.png)](https://travis-ci.org/zfegg/expressive-test)
[![Coverage Status](https://coveralls.io/repos/github/zfegg/expressive-test/badge.svg?branch=master)](https://coveralls.io/github/zfegg/expressive-test?branch=master)
[![Latest Stable Version](https://poser.pugx.org/zfegg/expressive-test/v/stable.png)](https://packagist.org/packages/zfegg/expressive-test)


Zend Expressive abstract test case for PHPUnit.

Zend Expressive 使用 PHPUnit 单元抽象测试工具

Installation / 安装使用
-----------------------

Install via composer.

```bash
composer require zfegg/expressive-test --dev
```

Usage / 使用
--------------

### `runApp(...)` in test.

```php

use Psr\Http\Message\ResponseInterface;
use Zfegg\ExpressiveTest\AbstractActionTestCase;

class HomePageTest extends AbstractActionTestCase {

    public function testHome() {
        $response = $this->runApp(
            'POST',
            '/?test=1',
            ['body' => '2'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            '{"a":"b"}',
            ['cookie' => '3']
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPassMiddlewareOrMockService() {

        $this->container->setService('some middleware', new PassMiddleware());
        
        $mock = $this->createMock(SomeService::class);
        $this->container->setService('mock some service', $mock);

        $response = $this->runApp(
            'POST',
            '/?test=1',
            ['body' => '2'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            '{"a":"b"}',
            ['cookie' => '3']
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
```

### Simple test methods like Laravel

```php

use Psr\Http\Message\ResponseInterface;
use Zfegg\ExpressiveTest\AbstractActionTestCase;

class HomePageTest extends AbstractActionTestCase {

    public function testHome() {
        /*
        $this->get($uri, $headers = []);
        $this->getJson($uri, $headers = []);
        $this->post($uri, $data = [], $headers = []);
        $this->postJson($uri, $data = [], $headers = []);
        $this->put($uri, $data = [], $headers = []);
        $this->putJson($uri, $data = [], $headers = []);
        $this->patch($uri, $data = [], $headers = []);
        $this->patchJson($uri, $data = [], $headers = []);
        $this->delete($uri, $data = [], $headers = []);
        $this->json($method, $uri, $data = [], $headers = []);
        $this->call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null);
        */
        $response = $this->getJson('/?test=1');
        $response->assertOk();
        $response->assertSuccessful();
    }

    public function testRedirectLogin() {
        $response = $this->getJson('/redirect');
        $response->assertRedirect('/login');
    }
}
```

### Test support methods

- `get($uri, $headers = [])`
- `getJson($uri, $headers = [])`
- `post($uri, $data = [], $headers = [])`
- `postJson($uri, $data = [], $headers = [])`
- `put($uri, $data = [], $headers = [])`
- `putJson($uri, $data = [], $headers = [])`
- `patch($uri, $data = [], $headers = [])`
- `patchJson($uri, $data = [], $headers = [])`
- `delete($uri, $data = [], $headers = [])`
- `json($method, $uri, $data = [], $headers = [])`
- `call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)`

### Response assert methods

- `assertCookie`
- `assertCookieExpired`
- `assertCookieMissing`
- `assertCookieNotExpired`
- `assertCreated`
- `assertDontSee`
- `assertDontSeeText`
- `assertExactJson`
- `assertForbidden`
- `assertHeader`
- `assertHeaderMissing`
- `assertJson`
- `assertJsonCount`
- `assertJsonMessage`
- `assertJsonMissing`
- `assertJsonMissingExact`
- `assertJsonPath`
- `assertJsonStructure`
- `assertLocation`
- `assertNoContent`
- `assertNotFound`
- `assertOk`
- `assertRedirect`
- `assertSee`
- `assertSeeText`
- `assertStatus`
- `assertSuccessful`
- `assertUnauthorized`


### `PassMiddleware`

For pass a middleware. As default it will pass [`ErrorHandler::class`](src/Helper/SetupApplicationTrait.php#55).

```php
use Psr\Http\Message\ResponseInterface;
use Zfegg\ExpressiveTest\AbstractActionTestCase;
use Zfegg\ExpressiveTest\PassMiddleware;

class HomePageTest extends AbstractActionTestCase {

    public function testHome() {
        // Pass a authentication middleware.
        $this->container->setService(AuthenticationMiddleware::class, PassMiddleware::class); 

        $response = $this->getJson('/api/users');
        $response->assertOk();
    }
}
```
