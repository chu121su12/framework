<?php

if (! \function_exists('phpunit_major_version')) {
    function phpunit_major_version()
    {
        if (\class_exists('PHPUnit_Runner_Version')) {
            return (int) \explode('.', \PHPUnit_Runner_Version::id())[0];
        }

        if (\class_exists('PHPUnit\Runner\Version')) {
            return (int) \explode('.', \PHPUnit\Runner\Version::id())[0];
        }
    }
}

// rm -rf ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
// mkdir -p ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
