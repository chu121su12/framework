<?php

namespace PHPUnit\Framework\Patch;

trait ExtraAssertions
{
    function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
    {
        static::assertSame(
            preg_replace('/\r\n/', "\n", trim($expected)),
            preg_replace('/\r\n/', "\n", trim($actual)),
            $message
        );
    }
}
