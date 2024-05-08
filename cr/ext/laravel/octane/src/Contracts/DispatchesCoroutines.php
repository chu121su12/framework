<?php

namespace Laravel\Octane\Contracts;

interface DispatchesCoroutines
{
    /**
     * Concurrently resolve the given callbacks via coroutines, returning the results.
     *
     * @param  int  $waitSeconds
     * @return array
     */
    public function resolve(array $coroutines, /*int */$waitSeconds = -1)/*: array*/;
}
