<?php

namespace Laravel\Octane\Exceptions;

use Exception;

class WorkerException extends Exception
{
    public function __construct(/*string */$message, /*int */$code, /*string */$file, /*int */$line)
    {
        $message = cast_to_string($message);

        $code = cast_to_int($code);

        $file = cast_to_string($file);

        $line = cast_to_int($line);

        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
    }
}
