<?php

namespace League\Flysystem\Patch;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;

class InvalidVisibilityProvided extends InvalidArgumentException implements FilesystemException
{
    public static function withVisibility(/*string */$visibility, /*string */$expectedMessage)/*: InvalidVisibilityProvided*/
    {
        $visibility = backport_type_check('string', $visibility);
        $expectedMessage = backport_type_check('string', $expectedMessage);

        $provided = var_export($visibility, true);
        $message = "Invalid visibility provided. Expected {$expectedMessage}, received {$provided}";

        throw new InvalidVisibilityProvided($message);
    }
}
