<?php

namespace Illuminate\Concurrency;

use Closure;
use Illuminate\Foundation\Defer\DeferredCallback;
use Illuminate\Process\Factory as ProcessFactory;
use Illuminate\Process\Pool;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\SerializableClosure;
use Symfony\Component\Process\PhpExecutableFinder;

class ProcessDriver
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
        $tasks = backport_type_check('Closure|array', $tasks);

        $php = (new PhpExecutableFinder)->find(false);

        $results = $this->processFactory->pool(function (Pool $pool) use ($tasks, $php) {
            foreach (Arr::wrap($tasks) as $task) {
                $pool->path(base_path())->env([
                    'LARAVEL_INVOKABLE_CLOSURE' => \base64_encode(backport_serialize(new SerializableClosure($task))),
                ])->command($php.' artisan invoke-serialized-closure --base64');
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
        $tasks = backport_type_check('Closure|array', $tasks);

        return defer(function () use ($tasks) {
            foreach (Arr::wrap($tasks) as $task) {
                $this->processFactory->path(base_path())->env([
                    'LARAVEL_INVOKABLE_CLOSURE' => backport_serialize(new SerializableClosure($task)),
                ])->run('php artisan invoke-serialized-closure 2>&1 &');
            }
        });
    }
}
