<?php

namespace PHPUnit\Framework\Patch;

trait ExtraAssertions
{
    public static function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
    {
        static::assertSame(
            preg_replace('/\r\n/', "\n", trim($expected)),
            preg_replace('/\r\n/', "\n", trim($actual)),
            $message
        );
    }

    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        cast_to_int($expectedCount, null, true);

        parent::assertCount($expectedCount, $haystack, $message);
    }
}
