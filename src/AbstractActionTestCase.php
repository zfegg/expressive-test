<?php

declare(strict_types=1);

namespace Zfegg\ExpressiveTest;

use PHPUnit\Framework\TestCase;

abstract class AbstractActionTestCase extends TestCase
{
    use Helper\MakeHttpRequestTrait;
    use Helper\SetupApplicationTrait;
}
