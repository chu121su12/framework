<?php

namespace Laravel\Octane\Contracts;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\OctaneResponse;
use Laravel\Octane\RequestContext;
use Throwable;

interface Client
{
    /**
     * Marshal the given request context into an Illuminate request.
     *
     * @return array
     */
    public function marshalRequest(RequestContext $context)/*: array*/;

    /**
     * Send the response to the server.
     *
     * @return void
     */
    public function respond(RequestContext $context, OctaneResponse $response)/*: void*/;

    /**
     * Send an error message to the server.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function error(/*Throwable */$e, Application $app, Request $request, RequestContext $context)/*: void*/;
}
