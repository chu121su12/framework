<?php

namespace Laravel\Octane;

use Laravel\Octane\Contracts\DispatchesTasks;
use Laravel\Octane\Exceptions\TaskExceptionResult;
use Throwable;

class SequentialTaskDispatcher implements DispatchesTasks
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
    public function resolve(array $tasks, /*int */$waitMilliseconds = 1) ////: array
    {
        $waitMilliseconds = cast_to_int($waitMilliseconds);

        return collect($tasks)->mapWithKeys(
            function ($task, $key) {
                $valueCallback = function () use ($task) {
                    try {
                        return $task();
                    } catch (\Exception $e) {
                    } catch (\Error $e) {
                    } catch (\Throwable $e) {
                    }

                    if (isset($e)) {
                        report($e);

                        return TaskExceptionResult::from($e);
                    }
                };

                return [$key => $valueCallback()];
            }
        )->each(function ($result) {
            if ($result instanceof TaskExceptionResult) {
                throw $result->getOriginal();
            }
        })->all();
    }

    /**
     * Concurrently dispatch the given callbacks via background tasks.
     *
     * @param  array  $tasks
     * @return void
     */
    public function dispatch(array $tasks) ////: void
    {
        try {
            $this->resolve($tasks);
        } catch (\Exceptions $e) {
        } catch (\Error $e) {
        } catch (\Throwable $e) {
        }

        if (isset($e)) {
            // ..
        }
    }
}
