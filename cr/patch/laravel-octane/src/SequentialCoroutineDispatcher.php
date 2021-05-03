<?php

namespace Laravel\Octane;

use Laravel\Octane\Contracts\DispatchesCoroutines;

class SequentialCoroutineDispatcher implements DispatchesCoroutines
{
    /**
     * Concurrently resolve the given callbacks via coroutines, returning the results.
     *
     * @param  array  $coroutines
     * @param  int  $waitSeconds
     * @return array
     */
    public function resolve(array $coroutines, /*int */$waitSeconds = -1) ////: array
    {
        $waitSeconds = cast_to_int($waitSeconds);

        return collect($coroutines)->mapWithKeys(
            function ($coroutine, $key) { return [$key => $coroutine()]; }
        )->all();
    }
}
