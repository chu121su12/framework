<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;

class TickReceived
{
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
