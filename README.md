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


```php

use Psr\Http\Message\ResponseInterface;
use Zfegg\ExpressiveTest\AbstractActionTestCase;

class HomePageTest extend AbstractActionTestCase {

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