<?php

namespace Laravel\Octane\Concerns;

use Laravel\Octane\Contracts\DispatchesTasks;
use Laravel\Octane\SequentialTaskDispatcher;
use Laravel\Octane\Swoole\ServerStateFile;
use Laravel\Octane\Swoole\SwooleHttpTaskDispatcher;
use Laravel\Octane\Swoole\SwooleTaskDispatcher;
use Swoole\Http\Server;

trait ProvidesConcurrencySupport
{
    /**
     * Concurrently resolve the given callbacks via background tasks, returning the results.
     *
     * Results will be keyed by their given keys - if a task did not finish, the tasks value will be "false".
     *
     * @param  array  $tasks
     * @param  int  $waitMilliseconds
     * @return array
     *
     * @throws \Laravel\Octane\Exceptions\TaskException
     * @throws \Laravel\Octane\Exceptions\TaskTimeoutException
     */
    public function concurrently(array $tasks, /*int */$waitMilliseconds = 3000)
    {
        $waitMilliseconds = cast_to_int($waitMilliseconds);

        return $this->tasks()->resolve($tasks, $waitMilliseconds);
    }

    /**
     * Get the task dispatcher.
     *
     * @return \Laravel\Octane\Contracts\DispatchesTasks
     */
    public function tasks()
    {
        return backport_match (true,
            [function () { return app()->bound(DispatchesTasks::class); }, function () { return app(DispatchesTasks::class); }],
            [function () { return app()->bound(Server::class); }, function () { return new SwooleTaskDispatcher; }],
            [function () { return class_exists(Server::class); }, function () {
                $serverState = app(ServerStateFile::class)->read();

                return new SwooleHttpTaskDispatcher(
                    isset($serverState['state']) && isset($serverState['state']['host']) ? $serverState['state']['host'] : '127.0.0.1',
                    isset($serverState['state']) && isset($serverState['state']['port']) ? $serverState['state']['port'] : '8000',
                    new SequentialTaskDispatcher
                )
            }],
            ['default' => null, function () { return new SequentialTaskDispatcher; }],
        );
    }
}
