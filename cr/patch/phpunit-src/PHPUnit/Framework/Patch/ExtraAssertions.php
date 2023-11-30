<?php

namespace PHPUnit\Framework\Patch;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;

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

    public static function assertEqualsCanonicalizing($expected, $actual, $message = '')
    {
        try {
            if ($expected === $actual) {
                return;
            }

            $comparatorFactory = ComparatorFactory::getInstance();

            $comparator = $comparatorFactory->getComparatorFor(
                $expected,
                $actual
            );

            $comparator->assertEquals(
                $expected,
                $actual,
                0.0,
                true,
                false
            );
        } catch (ComparisonFailure $f) {
            throw new ExpectationFailedException(
                \trim($message . "\n" . $f->getMessage()),
                $f
            );
        }
    }

    public static function skipWithInactiveSocketConnection(TestCase $phpunitInstance, $host, $port = 443, $timeout = 1)
    {
        phpunit_skip_with_inactive_socket_connection($phpunitInstance, $host, $port, $timeout);
    }
}
