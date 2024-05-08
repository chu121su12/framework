<?php

namespace Laravel\Octane\Exceptions;

use Exception;

class WorkerException extends Exception
{
    public function __construct(/*string */$message, /*int */$code, /*string */$file, /*int */$line)
    {
        $line = backport_type_check('int', $line);

        $file = backport_type_check('string', $file);

        $code = backport_type_check('int', $code);

        $message = backport_type_check('string', $message);

        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
    }
}
