<?php

namespace Laravel\Octane\Swoole;

class SignalDispatcher
{
    protected $extension;

    public function __construct(/*protected */SwooleExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * Determine if the given process ID can be communicated with.
     *
     * @param  int  $processId
     * @return bool
     */
    public function canCommunicateWith(/*int */$processId) ////: bool
    {
        $processId = cast_to_int($processId);

        return $this->signal($processId, 0);
    }

    /**
     * Send a SIGTERM signal to the given process.
     *
     * @param  int  $processId
     * @param  int  $wait
     * @return bool
     */
    public function terminate(/*int */$processId, /*int */$wait = 0) ////: bool
    {
        $processId = cast_to_int($processId);

        $wait = cast_to_int($wait);

        $this->extension->dispatchProcessSignal($processId, SIGTERM);

        if ($wait) {
            $start = time();

            do {
                if (! $this->canCommunicateWith($processId)) {
                    return true;
                }

                $this->extension->dispatchProcessSignal($processId, SIGTERM);

                sleep(1);
            } while (time() < $start + $wait);
        }

        return false;
    }

    /**
     * Send a signal to the given process.
     *
     * @param  int  $processId
     * @param  int  $signal
     * @return bool
     */
    public function signal(/*int */$processId, /*int */$signal) ////: bool
    {
        $processId = cast_to_int($processId);

        $signal = cast_to_int($signal);

        return $this->extension->dispatchProcessSignal($processId, $signal);
    }
}
