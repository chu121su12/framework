<?php

namespace Facade\FlareClient\Stacktrace;

use SplFileObject;

class File
{
    /** @var \SplFileObject */
    private $file;

    public function __construct(/*string */$path)
    {
        $path = cast_to_string($path);

        $this->file = new SplFileObject($path);
    }

    public function numberOfLines()/*: int*/
    {
        $this->file->seek(PHP_INT_MAX);

        return $this->file->key() + 1;
    }

    public function getLine(/*int */$lineNumber = null)/*: string*/
    {
        $lineNumber = cast_to_int($lineNumber);

        if (is_null($lineNumber)) {
            return $this->getNextLine();
        }

        $this->file->seek($lineNumber - 1);

        return $this->file->current();
    }

    public function getNextLine()/*: string*/
    {
        $this->file->next();

        return $this->file->current();
    }
}
