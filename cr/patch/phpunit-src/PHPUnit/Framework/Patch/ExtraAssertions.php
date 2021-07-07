<?php

namespace PHPUnit\Framework\Patch;

trait ExtraAssertions
{
    function assertSameStringDifferentLineEndings($expected, $actual, $message = '')
    {
        if (windows_os() || PHP_OS_FAMILY === 'Unknown') {
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
