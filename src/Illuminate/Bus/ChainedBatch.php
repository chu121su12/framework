<?php

namespace Illuminate\Bus;

use CR\LaravelBackport\ChainedBatchContainer;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Throwable;

class ChainedBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The collection of batched jobs.
     *
     * @var \Illuminate\Support\Collection
     */
    public /*Collection */$jobs;

    /**
     * The name of the batch.
     *
     * @var string
     */
    public /*string */$name;

    /**
     * The batch options.
     *
     * @var array
     */
    public /*array */$options;

    /**
     * Create a new chained batch instance.
     *
     * @param  \Illuminate\Bus\PendingBatch  $batch
     * @return void
     */
    public function __construct(PendingBatch $batch)
    {
        $this->jobs = static::prepareNestedBatches($batch->jobs);

        $this->name = $batch->name;
        $this->options = $batch->options;
    }

    /**
     * Prepare any nested batches within the given collection of jobs.
     *
     * @param  \Illuminate\Support\Collection  $jobs
     * @return \Illuminate\Support\Collection
     */
    public static function prepareNestedBatches(Collection $jobs)/*: Collection*/
    {
        return $jobs->map(function ($job) {
            switch (true) {
                case is_array($job): return static::prepareNestedBatches(collect($job))->all();
                case $job instanceof Collection: return static::prepareNestedBatches($job);
                case $job instanceof PendingBatch: return new ChainedBatch($job);
                default: return $job;
            }
        });
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->attachRemainderOfChainToEndOfBatch(
            $this->toPendingBatch()
        )->dispatch();
    }

    /**
     * Convert the chained batch instance into a pending batch.
     *
     * @return \Illuminate\Bus\PendingBatch
     */
    public function toPendingBatch()
    {
        $batch = Container::getInstance()->make(DispatcherContract::class)->batch($this->jobs);

        $batch->name = $this->name;
        $batch->options = $this->options;

        if ($this->queue) {
            $batch->onQueue($this->queue);
        }

        if ($this->connection) {
            $batch->onConnection($this->connection);
        }

        foreach (isset($this->chainCatchCallbacks) ? $this->chainCatchCallbacks : [] as $callback) {
            $batch->catch_(function (Batch $batch, /*?Throwable */$exception) use ($callback) {
                backport_type_throwable($exception, null);

                if (! $batch->allowsFailures()) {
                    $callback($exception);
                }
            });
        }

        return $batch;
    }

    /**
     * Move the remainder of the chain to a "finally" batch callback.
     *
     * @param  \Illuminate\Bus\PendingBatch  $batch
     * @return \Illuminate\Bus\PendingBatch
     */
    protected function attachRemainderOfChainToEndOfBatch(PendingBatch $batch)
    {
        if (! empty($this->chained)) {
            $next = unserialize(array_shift($this->chained));

            $next->chained = $this->chained;

            $next->onConnection($next->connection ?: $this->chainConnection);
            $next->onQueue($next->queue ?: $this->chainQueue);

            $next->chainConnection = $this->chainConnection;
            $next->chainQueue = $this->chainQueue;
            $next->chainCatchCallbacks = $this->chainCatchCallbacks;

            $batch->finally_(new ChainedBatchContainer($next));
            // $batch->finally_(function (Batch $batch) use ($next) {
            //     if (! $batch->cancelled()) {
            //         Container::getInstance()->make(DispatcherContract::class)->dispatch($next);
            //     }
            // });

            $this->chained = [];
        }

        return $batch;
    }
}
