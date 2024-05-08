<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;

class WorkerStopping
{
    public $app;

    public function __construct(/*public */Application $app)
    {
        $this->app = $app;
    }
}
