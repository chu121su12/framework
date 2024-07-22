<?php

namespace Illuminate\Tests\Container;

use PHPUnit\Framework\TestCase;

if (\version_compare(\PHP_VERSION, '7.0', '>=')) {
    include_once __DIR__.'/../../tests-stubs/AfterResolvingAttributeCallbackTest.php';
}
else
{
    class AfterResolvingAttributeCallbackTest extends TestCase {
        public function testNoop()
        {
            $this->assertTrue(true);
        }
    }
}
