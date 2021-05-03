<?php

namespace Laravel\Octane;

use Throwable;
use Whoops\Exception\Inspector;

class WorkerExceptionInspector extends Inspector
{
    protected $class;

    protected $trace;

    public function __construct(/*Throwable */$throwable, /*protected string */$class, /*protected array */$trace)
    {
        $this->class = cast_to_string($class);

        $this->trace = $trace;

        parent::__construct($throwable);
    }

    /**
     * Get the worker exception name.
     *
     * @return string
     */
    public function getExceptionName()
    {
        return $this->class;
    }

    /**
     * Get the worker exception trace.
     *
     * @param   \Throwable  $throwable
     * @return  array
     */
    public function getTrace($throwable)
    {
        return $this->trace;
    }
}
