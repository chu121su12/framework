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
     * @param  int  $waitMilliseconds
     * @return array
     *
     * @throws \Laravel\Octane\Exceptions\TaskException
     * @throws \Laravel\Octane\Exceptions\TaskTimeoutException
     */
    public function concurrently(array $tasks, /*int */$waitMilliseconds = 3000)
    {
        $waitMilliseconds = backport_type_check('int', $waitMilliseconds);

        return $this->tasks()->resolve($tasks, $waitMilliseconds);
    }

    /**
     * Get the task dispatcher.
     *
     * @return \Laravel\Octane\Contracts\DispatchesTasks
     */
    public function tasks()
    {
        switch (true) {
            case app()->bound(DispatchesTasks::class): return app(DispatchesTasks::class);
            case app()->bound(Server::class): return new SwooleTaskDispatcher;
            case class_exists(Server::class): return \call_user_func(function (array $serverState) { return new SwooleHttpTaskDispatcher(
                isset($serverState['state']) && isset($serverState['state']['host']) ? $serverState['state']['host'] : '127.0.0.1',
                isset($serverState['state']) && isset($serverState['state']['port']) ? $serverState['state']['port'] : '8000',
                new SequentialTaskDispatcher
            ); }, app(ServerStateFile::class)->read());
            default: return new SequentialTaskDispatcher;
        };
    }
}
