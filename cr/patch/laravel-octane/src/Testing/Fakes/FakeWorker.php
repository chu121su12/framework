<?php

namespace Laravel\Octane\Testing\Fakes;

use Laravel\Octane\RequestContext;
use Laravel\Octane\Worker;

class FakeWorker extends Worker
{
    public function run()
    {
        foreach ($this->client->requests as $request) {
            list($request, $context) = $this->client->marshalRequest(
                new RequestContext(['request' => $request])
            );

            $this->handle($request, $context);
        }
    }

    public function runTasks()
    {
        return collect($this->client->requests)->map(function ($data) { return $this->handleTask($data); })->all();
    }

    public function runTicks()
    {
        return collect($this->client->requests)->map(function () { return $this->handleTick(); })->all();
    }
}
