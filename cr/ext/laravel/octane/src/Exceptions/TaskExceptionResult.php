<?php

namespace Laravel\Octane\Exceptions;

use Laravel\SerializableClosure\Support\ClosureStream;

class TaskExceptionResult
{
    protected $class;
    protected $message;
    protected $code;
    protected $file;
    protected $line;

    public function __construct(
        /*protected string */$class,
        /*protected string */$message,
        /*protected int */$code,
        /*protected string */$file,
        /*protected int */$line
    ) {
        $this->class = backport_type_check('string', $class);
        $this->message = backport_type_check('string', $message);
        $this->code = backport_type_check('int', $code);
        $this->file = backport_type_check('string', $file);
        $this->line = backport_type_check('int', $line);
    }

    /**
     * Creates a new task exception result from the given throwable.
     *
     * @param  \Throwable  $throwable
     * @return \Laravel\Octane\Exceptions\TaskExceptionResult
     */
    public static function from($throwable)
    {
        $fallbackTrace = str_starts_with($throwable->getFile(), ClosureStream::STREAM_PROTO.'://')
            ? collect($throwable->getTrace())->whereNotNull('file')->first()
            : null;

        return new static(
            get_class($throwable),
            $throwable->getMessage(),
            (int) $throwable->getCode(),
            isset($fallbackTrace) && isset($fallbackTrace['file']) ? $fallbackTrace['file'] : $throwable->getFile(),
            isset($fallbackTrace) && isset($fallbackTrace['line']) ? $fallbackTrace['line'] : (int) $throwable->getLine()
        );
    }

    /**
     * Gets the original throwable.
     *
     * @return \Laravel\Octane\Exceptions\TaskException|\Laravel\Octane\Exceptions\DdException
     */
    public function getOriginal()
    {
        if ($this->class == DdException::class) {
            return new DdException(
                backport_json_decode($this->message, true)
            );
        }

        return new TaskException(
            $this->class,
            $this->message,
            (int) $this->code,
            $this->file,
            (int) $this->line
        );
    }
}
