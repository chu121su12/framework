<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Laravel\Octane\Contracts\OperationTerminated;

class TaskTerminated implements OperationTerminated
{
    use HasApplicationAndSandbox;

    public $app;
    public $sandbox;
    public $data;
    public $result;

    public function __construct(
        /*public */Application $app,
        /*public */Application $sandbox,
        /*public */$data,
        /*public */$result
    ) {
        $this->app = $app;
        $this->sandbox = $sandbox;
        $this->data = $data;
        $this->result = $result;
    }
}
