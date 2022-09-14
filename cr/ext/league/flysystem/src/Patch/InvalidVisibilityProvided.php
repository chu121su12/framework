<?php

namespace League\Flysystem\Patch;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;

class InvalidVisibilityProvided extends InvalidArgumentException implements FilesystemException
{
    public static function withVisibility(/*string */$visibility, /*string */$expectedMessage)/*: InvalidVisibilityProvided*/
    {
        $visibility = cast_to_string($visibility);
        $expectedMessage = cast_to_string($expectedMessage);

        $provided = var_export($visibility, true);
        $message = "Invalid visibility provided. Expected {$expectedMessage}, received {$provided}";

        throw new InvalidVisibilityProvided($message);
    }
}
