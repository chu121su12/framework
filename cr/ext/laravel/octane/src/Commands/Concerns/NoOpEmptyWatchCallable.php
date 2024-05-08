<?php

namespace Laravel\Octane\Commands\Concerns;

class NoOpEmptyWatchCallable
{
    public function __call($method, $parameters)
    {
        return null;
    }
}
