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

// rm -rf ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
// mkdir -p ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
