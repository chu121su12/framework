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
        backport_type_throwable($throwable);

        $this->class = backport_type_check('string', $class);

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
     * @param  \Throwable  $throwable
     * @return array
     */
    public function getTrace($throwable)
    {
        return $this->trace;
    }
}
