<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Throwable;

class WorkerErrorOccurred
{
    public $exception;

    public $sandbox;

    public function __construct(/*public Throwable */$exception, /*public */Application $sandbox)
    {
        $this->exception = $exception;

        $this->sandbox = $sandbox;
    }
}
