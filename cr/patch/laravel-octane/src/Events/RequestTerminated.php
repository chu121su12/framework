<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\Contracts\OperationTerminated;
use Symfony\Component\HttpFoundation\Response;

class RequestTerminated implements OperationTerminated
{
    use HasApplicationAndSandbox;

    public $app;
    public $sandbox;
    public $request;
    public $response;

    public function __construct(
        /*public */Application $app,
        /*public */Application $sandbox,
        /*public */Request $request,
        /*public */Response $response
    ) {
        $this->app = $app;
        $this->sandbox = $sandbox;
        $this->request = $request;
        $this->response = $response;
    }
}
