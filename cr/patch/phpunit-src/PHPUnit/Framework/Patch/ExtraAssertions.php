<?php

namespace PHPUnit\Framework\Patch;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_ExpectationFailedException;

trait ExtraAssertions
{
    public static function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
    {
        try {
            return parent::assertThat($value, $constraint, $message);
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                $e->getMessage(),
                $e->getComparisonFailure(),
                $e->getPrevious()
            );
        }
    }

    public static function fail($message = '')
    {
        throw new AssertionFailedError($message);
    }

    public static function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
    {
        static::assertSame(
            preg_replace('/\r\n/', "\n", trim($expected)),
            preg_replace('/\r\n/', "\n", trim($actual)),
            $message
        );
    }
}
