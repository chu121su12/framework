<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Laravel\Octane\Contracts\OperationTerminated;

class TickTerminated implements OperationTerminated
{
    use HasApplicationAndSandbox;

    public $app;
    public $sandbox;

    public function __construct(
        /*public */Application $app,
        /*public */Application $sandbox
    ) {
        $this->app = $app;
        $this->sandbox = $sandbox;
    }
}
