<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;

class TaskReceived
{
    public $app;
    public $sandbox;
    public $data;

    public function __construct(
        /*public */Application $app,
        /*public */Application $sandbox,
        /*public */$data
    ) {
        $this->app = $app;
        $this->sandbox = $sandbox;
        $this->data = $data;
    }
}
