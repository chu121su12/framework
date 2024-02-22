<?php

namespace CR\LaravelBackport;

use Illuminate\Bus\Batch;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Container\Container;

class ChainedBatchContainer
{
    protected $next;

    public function __construct($next)
    {
        $this->next = $next;
    }

    public function __invoke(Batch $batch)
    {
        if (! $batch->cancelled()) {
            Container::getInstance()->make(DispatcherContract::class)->dispatch($this->next);
        }
    }
}
