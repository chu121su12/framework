<?php

namespace Laravel\Octane\Exceptions;

use Exception;

class WorkerException extends Exception
{
    public function __construct(/*string */$message, /*int */$code, /*string */$file, /*int */$line)
    {
        $message = backport_type_check('string', $message);

        $code = backport_type_check('int', $code);

        $file = backport_type_check('string', $file);

        $line = backport_type_check('int', $line);

        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
    }
}
