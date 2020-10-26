<?php

namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\IsEqualWithDelta;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;

if (phpunit_major_version() > 5) {
    trait PhpUnit8Assert
    {
        function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
        {
            if (windows_os()) {
                static::assertSame(
                    preg_replace('/\r\n/', "\n", trim($expected)),
                    preg_replace('/\r\n/', "\n", trim($actual)),
                    $message
                );

            } else {
                static::assertSame($expected, $actual, $message);
            }
        }
    }

} else {
    trait PhpUnit8Assert
    {
        function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
        {
            if (windows_os()) {
                static::assertSame(
                    preg_replace('/\r\n/', "\n", trim($expected)),
                    preg_replace('/\r\n/', "\n", trim($actual)),
                    $message
                );

            } else {
                static::assertSame($expected, $actual, $message);
            }
        }

        /**
         * Asserts that two variables are equal (with delta).
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @see Assert::assertEqualsWithDelta
         */
        function assertEqualsWithDelta($expected, $actual, $delta, $message = '')
        {
            $constraint = new IsEqualWithDelta(
                $expected,
                $delta
            );

            static::assertThat($actual, $constraint, $message);
        }

        /**
         * Asserts that a variable is of type array.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert array $actual
         */
        public static function assertIsArray($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_ARRAY),
                $message
            );
        }

        /**
         * Asserts that a variable is of type bool.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert bool $actual
         */
        public static function assertIsBool($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_BOOL),
                $message
            );
        }

        /**
         * Asserts that a variable is of type float.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert float $actual
         */
        public static function assertIsFloat($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_FLOAT),
                $message
            );
        }

        /**
         * Asserts that a variable is of type int.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert int $actual
         */
        public static function assertIsInt($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_INT),
                $message
            );
        }

        /**
         * Asserts that a variable is of type numeric.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert numeric $actual
         */
        public static function assertIsNumeric($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_NUMERIC),
                $message
            );
        }

        /**
         * Asserts that a variable is of type object.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert object $actual
         */
        public static function assertIsObject($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_OBJECT),
                $message
            );
        }

        /**
         * Asserts that a variable is of type string.
         *
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         *
         * @psalm-assert string $actual
         */
        public static function assertIsString($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_STRING),
                $message
            );
        }

        /**
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public static function assertStringContainsString($needle, $haystack, $message = '')
        {
            $constraint = new StringContains($needle, false);

            static::assertThat($haystack, $constraint, $message);
        }

        /**
         * @throws ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public static function assertStringNotContainsString($needle, $haystack, $message = '')
        {
            $constraint = new LogicalNot(new StringContains($needle));

            static::assertThat($haystack, $constraint, $message);
        }

        public static function assertIsCallable($actual, $message = '')
        {
            static::assertThat(
                $actual,
                new IsType(IsType::TYPE_CALLABLE),
                $message
            );
        }
    }
}
