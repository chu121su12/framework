<?php

namespace Laravel\Octane\Swoole;

use Laravel\Octane\Contracts\DispatchesCoroutines;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;

class SwooleCoroutineDispatcher implements DispatchesCoroutines
{
    protected $withinCoroutineContext;

    public function __construct(/*protected bool */$withinCoroutineContext)
    {
        $this->withinCoroutineContext = cast_to_bool($withinCoroutineContext);
    }

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

        $results = [];

        $callback = function () use (&$results, $coroutines, $waitSeconds) {
            $waitGroup = new WaitGroup;

            foreach ($coroutines as $key => $callback) {
                Coroutine::create(function () use ($key, $callback, $waitGroup, &$results) {
                    $waitGroup->add();

                    $results[$key] = $callback();

                    $waitGroup->done();
                });
            }

            $waitGroup->wait($waitSeconds);
        };

        if (! $this->withinCoroutineContext) {
            Coroutine\run($callback);
        } else {
            $callback();
        }

        return $results;
    }
}
