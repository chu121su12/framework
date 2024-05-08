<?php

namespace Laravel\Octane\Swoole;

use Closure;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Laravel\Octane\Contracts\DispatchesTasks;
use Laravel\Octane\Exceptions\TaskExceptionResult;
use Laravel\Octane\Exceptions\TaskTimeoutException;
use Laravel\SerializableClosure\SerializableClosure;

class SwooleHttpTaskDispatcher implements DispatchesTasks
{
    protected $host;
    protected $port;
    protected $fallbackDispatcher;

    public function __construct(
        /*protected string */$host,
        /*protected string */$port,
        /*protected */DispatchesTasks $fallbackDispatcher
    ) {
        $this->host = backport_type_check('string', $host);
        $this->port = backport_type_check('string', $port);
        $this->fallbackDispatcher = $fallbackDispatcher;
    }

    /**
     * Concurrently resolve the given callbacks via background tasks, returning the results.
     *
     * Results will be keyed by their given keys - if a task did not finish, the tasks value will be "false".
     *
     * @param  int  $waitMilliseconds
     * @return array
     *
     * @throws \Laravel\Octane\Exceptions\TaskException
     * @throws \Laravel\Octane\Exceptions\TaskTimeoutException
     */
    public function resolve(array $tasks, /*int */$waitMilliseconds = 3000)/*: array*/
    {
        $waitMilliseconds = backport_type_check('int', $waitMilliseconds);

        $tasks = collect($tasks)->mapWithKeys(function ($task, $key) {
            return [$key => $task instanceof Closure
                            ? new SerializableClosure($task)
                            : $task, ];
        })->all();

        try {
            $response = Http::timeout(($waitMilliseconds / 1000) + 5)->post("http://{$this->host}:{$this->port}/octane/resolve-tasks", [
                'tasks' => Crypt::encryptString(backport_serialize($tasks)),
                'wait' => $waitMilliseconds,
            ]);

            switch ($response->status()) {
                case 200: return backport_unserialize($response);
                case 504: throw TaskTimeoutException::after($waitMilliseconds);
                default: throw TaskExceptionResult::from(
                    new Exception('Invalid response from task server.')
                )->getOriginal();
            }
        } catch (ConnectionException $_e) {
            return $this->fallbackDispatcher->resolve($tasks, $waitMilliseconds);
        }
    }

    /**
     * Concurrently dispatch the given callbacks via background tasks.
     *
     * @return void
     */
    public function dispatch(array $tasks)/*: void*/
    {
        $tasks = collect($tasks)->mapWithKeys(function ($task, $key) {
            return [$key => $task instanceof Closure
                            ? new SerializableClosure($task)
                            : $task, ];
        })->all();

        try {
            Http::post("http://{$this->host}:{$this->port}/octane/dispatch-tasks", [
                'tasks' => Crypt::encryptString(backport_serialize($tasks)),
            ]);
        } catch (ConnectionException $_e) {
            $this->fallbackDispatcher->dispatch($tasks);
        }
    }
}
