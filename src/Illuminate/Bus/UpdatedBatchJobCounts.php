<?php

namespace Illuminate\Bus;

class UpdatedBatchJobCounts
{
    /**
     * The number of pending jobs remaining for the batch.
     *
     * @var int
     */
    public $pendingJobs;

    /**
     * The number of failed jobs that belong to the batch.
     *
     * @var int
     */
    public $failedJobs;

    /**
     * Create a new batch job counts object.
     *
     * @param  int  $pendingJobs
     * @param  int  $failedJobs
     * @return void
     */
    public function __construct($pendingJobs, $failedJobs)
    {
        $failedJobs = cast_to_int($failedJobs);

        $pendingJobs = cast_to_int($pendingJobs);

        $this->pendingJobs = $pendingJobs;
        $this->failedJobs = $failedJobs;
    }

    /**
     * Determine if all jobs have ran exactly once.
     *
     * @return bool
     */
    public function allJobsHaveRanExactlyOnce()
    {
        return ($this->pendingJobs - $this->failedJobs) === 0;
    }
}
