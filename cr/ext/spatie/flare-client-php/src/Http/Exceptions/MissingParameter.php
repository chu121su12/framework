<?php

namespace Spatie\FlareClient\Http\Exceptions;

use Exception;

class MissingParameter extends Exception
{
    public static function create(/*string */$parameterName)/*: self*/
    {
        $parameterName = backport_type_check('string', $parameterName);

        return new self("`$parameterName` is a required parameter");
    }
}
