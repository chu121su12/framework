<?php

namespace PHPUnit\Framework;

use PHPUnit\MockObject\PHPUnit10MockBuilder;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    use PhpUnit8Expect;

    public function getMockBuilder($className)
    {
        if (phpunit_major_version() <= 5) {
            return new PHPUnit10MockBuilder($this, $className);
        }

        return parent::getMockBuilder($className);
    }
}
