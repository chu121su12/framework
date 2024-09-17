<?php

namespace Illuminate\Concurrency;

use Closure;
use Illuminate\Console\Application;
use Illuminate\Contracts\Concurrency\Driver;
use Illuminate\Foundation\Defer\DeferredCallback;
use Illuminate\Process\Factory as ProcessFactory;
use Illuminate\Process\Pool;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\SerializableClosure;

class ProcessDriver implements Driver
{
    protected $processFactory;

    /**
     * Create a new process based concurrency driver.
     */
    public function __construct(/*protected */ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;

        //
    }

    /**
     * Run the given tasks concurrently and return an array containing the results.
     */
    public function run(/*Closure|array */$tasks)/*: array*/
    {
        $command = Application::formatCommandString('invoke-serialized-closure');

        $results = $this->processFactory->pool(function (Pool $pool) use ($tasks, $command) {
            foreach (Arr::wrap($tasks) as $task) {
                $pool->path(base_path())->env([
                    'LARAVEL_INVOKABLE_CLOSURE' => serialize(new SerializableClosure($task)),
                ])->command($command);
            }
        })->start()->wait();

        return $results->collect()->map(function ($result) {
            $result = backport_json_decode($result->output(), true);

            if (! $result['successful']) {
                $exceptionClass = $result['exception'] ?: 'Exception';

                throw new $exceptionClass(
                    $result['message']
                );
            }

            return backport_unserialize($result['result']);
        })->all();
    }

    /**
     * Start the given tasks in the background after the current task has finished.
     */
    public function defer(/*Closure|array */$tasks)/*: DeferredCallback*/
    {
        $command = Application::formatCommandString('invoke-serialized-closure');

        return defer(function () use ($tasks, $command) {
            foreach (Arr::wrap($tasks) as $task) {
                $this->processFactory->path(base_path())->env([
                    'LARAVEL_INVOKABLE_CLOSURE' => serialize(new SerializableClosure($task)),
                ])->run($command.' 2>&1 &');
            }
        });
    }
}
