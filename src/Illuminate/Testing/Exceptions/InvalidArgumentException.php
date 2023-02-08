<?php

namespace Illuminate\Testing\Exceptions;

use PHPUnit_Framework_Exception;

class InvalidArgumentException extends PHPUnit_Framework_Exception
{
    /**
     * Creates a new exception for an invalid argument.
     *
     * @return static
     */
    public static function create(/*int */$argument, /*string */$type)/*: static*/
    {
        $argument = backport_type_check('int', $argument);

        $type = backport_type_check('string', $type);

        $stack = debug_backtrace();

        $function = $stack[1]['function'];

        if (isset($stack[1]['class'])) {
            $function = sprintf('%s::%s', $stack[1]['class'], $stack[1]['function']);
        }

        return new static(
            sprintf(
                'Argument #%d of %s() must be %s %s',
                $argument,
                $function,
                in_array(lcfirst($type)[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a',
                $type
            )
        );
    }
}
