<?php

namespace Illuminate\Concurrency;

use Closure;
use Illuminate\Foundation\Defer\DeferredCallback;
use Illuminate\Support\Arr;
use Spatie\Fork\Fork;

class ForkDriver
{
    /**
     * Run the given tasks concurrently and return an array containing the results.
     */
    public function run(/*Closure|array */$tasks)/*: array*/
    {
        $tasks = backport_type_check('Closure|array', $tasks);

        /** @phpstan-ignore class.notFound */
        return Fork::new()->run(...Arr::wrap($tasks));
    }

    /**
     * Start the given tasks in the background after the current task has finished.
     */
    public function defer(/*Closure|array */$tasks)/*: DeferredCallback*/
    {
        $tasks = backport_type_check('Closure|array', $tasks);

        return defer(function () use ($tasks) { return $this->run($tasks); });
    }
}
