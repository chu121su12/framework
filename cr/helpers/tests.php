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

if (! \function_exists('phpunit_skip_with_inactive_socket_connection')) {
    function phpunit_skip_with_inactive_socket_connection(\PHPUnit\Framework\TestCase $phpunitInstance, $host, $port = 443, $timeout = 1)
    {
        try {
            $errorCode = null;
            $errorMessage = null;
            if ($connection = @fsockopen($host, $port, $errorCode, $errorMessage, $timeout)) {
                fclose($connection);

                return;
            }
        } catch (\Exception $e) {
        } catch (\Error $e) {
        } catch (\Throwable $e) {
        }

        $phpunitInstance->markTestSkipped('Network connection may not be available.');
    }
}

// rm -rf ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
// mkdir -p ./vendor/orchestra/testbench-core/laravel/bootstrap/cache
