<?php

namespace Laravel\Octane\Swoole\Actions;

use Laravel\Octane\Swoole\SwooleExtension;

class EnsureRequestsDontExceedMaxExecutionTime
{
    protected $extension;
    protected $timerTable;
    protected $maxExecutionTime;

    public function __construct(
        /*protected */SwooleExtension $extension,
        /*protected */$timerTable,
        /*protected */$maxExecutionTime
    ) {
        $this->extension = $extension;
        $this->timerTable = $timerTable;
        $this->maxExecutionTime = $maxExecutionTime;
    }

    /**
     * Invoke the action.
     *
     * @return void
     */
    public function __invoke()
    {
        foreach ($this->timerTable as $workerId => $row) {
            if ((time() - $row['time']) > $this->maxExecutionTime) {
                $this->timerTable->del($workerId);

                $this->extension->dispatchProcessSignal($row['worker_pid'], SIGKILL);
            }
        }
    }
}
