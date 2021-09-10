<?php

namespace Facade\FlareClient\Http\Exceptions;

use Exception;

class MissingParameter extends Exception
{
    public static function create(/*string */$parameterName)
    {
        $parameterName = cast_to_string($parameterName);

        return new static("`$parameterName` is a required parameter");
    }
}
