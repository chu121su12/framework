<?php

namespace Illuminate\Bus;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonSerializable;

class Batch implements Arrayable, JsonSerializable
{
    /**
     * The queue factory implementation.
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $queue;

    /**
     * The repository implementation.
     *
     * @var \Illuminate\Bus\BatchRepository
     */
    protected $repository;

    /**
     * The batch ID.
     *
     * @var string
     */
    public $id;

    /**
     * The batch name.
     *
     * @var string
     */
    public $name;

    /**
     * The total number of jobs that belong to the batch.
     *
     * @var int
     */
    public $totalJobs;

    /**
     * The total number of jobs that are still pending.
     *
     * @var int
     */
    public $pendingJobs;

    /**
     * The total number of jobs that have failed.
     *
     * @var int
     */
    public $failedJobs;

    /**
     * The IDs of the jobs that have failed.
     *
     * @var array
     */
    public $failedJobIds;

    /**
     * The batch options.
     *
     * @var array
     */
    public $options;

    /**
     * The date indicating when the batch was created.
     *
     * @var \Illuminate\Support\CarbonImmutable
     */
    public $createdAt;

    /**
     * The date indicating when the batch was cancelled.
     *
     * @var \Illuminate\Support\CarbonImmutable|null
     */
    public $cancelledAt;

    /**
     * The date indicating when the batch was finished.
     *
     * @var \Illuminate\Support\CarbonImmutable|null
     */
    public $finishedAt;

    /**
     * Create a new batch instance.
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @param  \Illuminate\Bus\BatchRepository  $repository
     * @param  string  $id
     * @param  string  $name
     * @param  int  $totalJobs
     * @param  int  $pendingJobs
     * @param  int  $failedJobs
     * @param  array  $failedJobIds
     * @param  array  $options
     * @param  \Illuminate\Support\CarbonImmutable  $createdAt
     * @param  \Illuminate\Support\CarbonImmutable|null  $cancelledAt
     * @param  \Illuminate\Support\CarbonImmutable|null  $finishedAt
     * @return void
     */
    public function __construct(QueueFactory $queue,
                                BatchRepository $repository,
                                $id,
                                $name,
                                $totalJobs,
                                $pendingJobs,
                                $failedJobs,
                                array $failedJobIds,
                                array $options,
                                CarbonImmutable $createdAt,
                                CarbonImmutable $cancelledAt = null,
                                CarbonImmutable $finishedAt = null)
    {
        $id = cast_to_string($id);

        $name = cast_to_string($name);

        $totalJobs = cast_to_int($totalJobs);

        $pendingJobs = cast_to_int($pendingJobs);

        $failedJobs = cast_to_int($failedJobs);

        $this->queue = $queue;
        $this->repository = $repository;
        $this->id = $id;
        $this->name = $name;
        $this->totalJobs = $totalJobs;
        $this->pendingJobs = $pendingJobs;
        $this->failedJobs = $failedJobs;
        $this->failedJobIds = $failedJobIds;
        $this->options = $options;
        $this->createdAt = $createdAt;
        $this->cancelledAt = $cancelledAt;
        $this->finishedAt = $finishedAt;
    }

    /**
     * Get a fresh instance of the batch represented by this ID.
     *
     * @return self
     */
    public function fresh()
    {
        return $this->repository->find($this->id);
    }

    /**
     * Add additional jobs to the batch.
     *
     * @param  \Illuminate\Support\Collection|array  $jobs
     * @return self
     */
    public function add($jobs)
    {
        $jobs = Collection::wrap($jobs);

        $jobs->each->withBatchId($this->id);

        $this->repository->transaction(function () use ($jobs) {
            $this->repository->incrementTotalJobs($this->id, count($jobs));

            $this->queue->connection(isset($this->options['connection']) ? $this->options['connection'] : null)->bulk(
                $jobs->all(),
                $data = '',
                isset($this->options['queue']) ? $this->options['queue'] : null
            );
        });

        return $this->fresh();
    }

    /**
     * Get the total number of jobs that have been processed by the batch thus far.
     *
     * @return int
     */
    public function processedJobs()
    {
        return $this->totalJobs - $this->pendingJobs;
    }

    /**
     * Get the percentage of jobs that have been processed (between 0-100).
     *
     * @return int
     */
    public function progress()
    {
        return $this->totalJobs > 0 ? round(($this->processedJobs() / $this->totalJobs) * 100) : 0;
    }

    /**
     * Record that a job within the batch finished successfully, executing any callbacks if necessary.
     *
     * @param  string  $jobId
     * @return void
     */
    public function recordSuccessfulJob($jobId)
    {
        $jobId = cast_to_string($jobId);

        $counts = $this->decrementPendingJobs($jobId);

        if ($counts->pendingJobs === 0) {
            $this->repository->markAsFinished($this->id);
        }

        if ($counts->pendingJobs === 0 && $this->hasThenCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['then'])->each->__invoke($batch);
        }

        if ($counts->allJobsHaveRanExactlyOnce() && $this->hasFinallyCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['finally'])->each->__invoke($batch);
        }
    }

    /**
     * Decrement the pending jobs for the batch.
     *
     * @param  string  $jobId
     * @return int
     */
    public function decrementPendingJobs($jobId)
    {
        $jobId = cast_to_string($jobId);

        return $this->repository->decrementPendingJobs($this->id, $jobId);
    }

    /**
     * Determine if the batch has finished executing.
     *
     * @return bool
     */
    public function finished()
    {
        return ! is_null($this->finishedAt);
    }

    /**
     * Determine if the batch has "success" callbacks.
     *
     * @return bool
     */
    public function hasThenCallbacks()
    {
        return isset($this->options['then']) && ! empty($this->options['then']);
    }

    /**
     * Determine if the batch allows jobs to fail without cancelling the batch.
     *
     * @return bool
     */
    public function allowsFailures()
    {
        return Arr::get($this->options, 'allowFailures', false) === true;
    }

    /**
     * Determine if the batch has job failures.
     *
     * @return bool
     */
    public function hasFailures()
    {
        return $this->failedJobs > 0;
    }

    /**
     * Record that a job within the batch failed to finish successfully, executing any callbacks if necessary.
     *
     * @param  string  $jobId
     * @param  \Throwable  $e
     * @return void
     */
    public function recordFailedJob($jobId, $e)
    {
        $jobId = cast_to_string($jobId);

        $counts = $this->incrementFailedJobs($jobId);

        if ($counts->failedJobs === 1 && ! $this->allowsFailures()) {
            $this->cancel();
        }

        if ($counts->failedJobs === 1 && $this->hasCatchCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['catch'])->each->__invoke($batch, $e);
        }

        if ($counts->allJobsHaveRanExactlyOnce() && $this->hasFinallyCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['finally'])->each->__invoke($batch, $e);
        }
    }

    /**
     * Increment the failed jobs for the batch.
     *
     * @param  string  $jobId
     * @return int
     */
    public function incrementFailedJobs($jobId)
    {
        $jobId = cast_to_string($jobId);

        return $this->repository->incrementFailedJobs($this->id, $jobId);
    }

    /**
     * Determine if the batch has "catch" callbacks.
     *
     * @return bool
     */
    public function hasCatchCallbacks()
    {
        return isset($this->options['catch']) && ! empty($this->options['catch']);
    }

    /**
     * Determine if the batch has "then" callbacks.
     *
     * @return bool
     */
    public function hasFinallyCallbacks()
    {
        return isset($this->options['finally']) && ! empty($this->options['finally']);
    }

    /**
     * Cancel the batch.
     *
     * @return void
     */
    public function cancel()
    {
        $this->repository->cancel($this->id);
    }

    /**
     * Determine if the batch has been cancelled.
     *
     * @return bool
     */
    public function canceled()
    {
        return $this->cancelled();
    }

    /**
     * Determine if the batch has been cancelled.
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->cancelledAt);
    }

    /**
     * Delete the batch from storage.
     *
     * @return void
     */
    public function delete()
    {
        $this->repository->delete($this->id);
    }

    /**
     * Convert the batch to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'totalJobs' => $this->totalJobs,
            'pendingJobs' => $this->pendingJobs,
            'processedJobs' => $this->processedJobs(),
            'progress' => $this->progress(),
            'failedJobs' => $this->failedJobs,
            'options' => $this->options,
            'createdAt' => $this->createdAt,
            'cancelledAt' => $this->cancelledAt,
            'finishedAt' => $this->finishedAt,
        ];
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
