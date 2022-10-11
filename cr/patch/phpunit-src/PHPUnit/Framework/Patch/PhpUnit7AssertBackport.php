<?php

namespace PHPUnit\Framework\Patch;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LogicalNot;

trait PhpUnit7AssertBackport
{
    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        backport_type_check('?int', $expectedCount, true);

        parent::assertCount($expectedCount, $haystack, $message);
    }

    /**
     * Asserts that a variable is not of type array.
     */
    public static function assertIsNotArray($actual, $message = '')
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_ARRAY)),
            $message
        );
    }
}
