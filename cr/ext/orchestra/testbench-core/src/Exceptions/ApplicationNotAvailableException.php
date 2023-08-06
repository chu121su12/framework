<?php

namespace Orchestra\Testbench\Exceptions;

use RuntimeException;

class ApplicationNotAvailableException extends RuntimeException
{
    /**
     * Make new RuntimeException when application is not available.
     *
     * @param  string  $method
     * @return static
     */
    public static function make(/*string */$method)
    {
        $method = backport_type_check('string', $method);

        return new static("Application is not available to run [{$method}]");
    }
}
