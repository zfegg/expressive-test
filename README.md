# expressive-test
Zend Expressive abstract test case for PHPUnit.

Zend Expressive 使用 PHPUnit 单元抽象测试工具

Usage / 安装使用
------

```bash
composer require zfegg/expressive-test --dev
```

Example / 例子
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
}
```