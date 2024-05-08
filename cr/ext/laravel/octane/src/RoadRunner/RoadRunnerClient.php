<?php

namespace Laravel\Octane\RoadRunner;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\Contracts\Client;
use Laravel\Octane\Contracts\StoppableClient;
use Laravel\Octane\MarshalsPsr7RequestsAndResponses;
use Laravel\Octane\Octane;
use Laravel\Octane\OctaneResponse;
use Laravel\Octane\RequestContext;
use Spiral\RoadRunner\Http\PSR7Worker;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RoadRunnerClient implements Client, StoppableClient
{
    use MarshalsPsr7RequestsAndResponses;

    protected $client;

    public function __construct(/*protected */PSR7Worker $client)
    {
        $this->client = $client;
    }

    /**
     * Marshal the given request context into an Illuminate request.
     *
     * @return array
     */
    public function marshalRequest(RequestContext $context)/*: array*/
    {
        return [
            $this->toHttpFoundationRequest($context->psr7Request),
            $context,
        ];
    }

    /**
     * Send the response to the server.
     *
     * @return void
     */
    public function respond(RequestContext $context, OctaneResponse $octaneResponse)/*: void*/
    {
        if ($octaneResponse->outputBuffer &&
            ! $octaneResponse->response instanceof StreamedResponse &&
            ! $octaneResponse->response instanceof BinaryFileResponse) {
            $octaneResponse->response->setContent(
                $octaneResponse->outputBuffer.$octaneResponse->response->getContent()
            );
        }

        $this->client->respond($this->toPsr7Response($octaneResponse->response));
    }

    /**
     * Send an error message to the server.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function error(/*Throwable */$e, Application $app, Request $request, RequestContext $context)/*: void*/
    {
        backport_type_throwable($e);

        $this->client->getWorker()->error(Octane::formatExceptionForClient(
            $e,
            $app->make('config')->get('app.debug')
        ));
    }

    /**
     * Stop the underlying server / worker.
     *
     * @return void
     */
    public function stop()/*: void*/
    {
        $this->client->getWorker()->stop();
    }
}
