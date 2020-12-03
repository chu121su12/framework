<?php

namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\IsEqualWithDelta;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;

if (phpunit_major_version() > 5) {
    trait PhpUnit8Assert
    {
        use Patch\ExtraAssertions;
    }

} else {
    trait PhpUnit8Assert
    {
        use Patch\ExtraAssertions;
        use Patch\PhpUnit5AssertBackport;
    }
}
