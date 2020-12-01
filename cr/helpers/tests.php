<?php

if (! \function_exists('phpunit_major_version')) {
    if (\class_exists('PHPUnit_Runner_Version')) {
        function phpunit_major_version()
        {
            return (int) \explode('.', \PHPUnit_Runner_Version::id())[0];
        }
    } else {
        function phpunit_major_version()
        {
            return (int) \explode('.', \PHPUnit\Runner\Version::id())[0];
        }
    }
}

if (! \function_exists('phpunit_assert_v5_skip_test')) {
    function phpunit_assert_v5_skip_test($instance)
    {
        if (phpunit_major_version() <= 5) {
            $instance->markTestSkipped('Cannot run test with PHPUnit 5.');
        }
    }
}
