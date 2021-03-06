<?php

namespace PHPUnit\Framework;

use PHPUnit\MockObject\PHPUnit10MockBuilder;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use PhpUnit8Expect;

    public function getMockBuilder($className)
    {
        return new PHPUnit10MockBuilder($this, $className);
    }
}
