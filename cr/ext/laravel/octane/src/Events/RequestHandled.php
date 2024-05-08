<?php

namespace Laravel\Octane\Events;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandled
{
    public $sandbox;
    public $request;
    public $response;

    public function __construct(
        /*public */Application $sandbox,
        /*public */Request $request,
        /*public */Response $response
    ) {
        $this->sandbox = $sandbox;
        $this->request = $request;
        $this->response = $response;
    }
}
