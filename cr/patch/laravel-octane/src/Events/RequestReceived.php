<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class RequestReceived
{
    public $app;
    public $sandbox;
    public $request;

    public function __construct(
        /*public */Application $app,
        /*public */Application $sandbox,
        /*public */Request $request
    ) {
        $this->app = $app;
        $this->sandbox = $sandbox;
        $this->request = $request;
    }
}
