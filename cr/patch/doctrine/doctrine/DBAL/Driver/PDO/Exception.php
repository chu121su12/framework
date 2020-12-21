<?php

namespace Doctrine\DBAL\Driver\PDO;

if (phpunit_major_version() <= 5) {
    class Exception
    {
        public static function new_($exception)
        {
            throw $exception;
        }
    }
}
