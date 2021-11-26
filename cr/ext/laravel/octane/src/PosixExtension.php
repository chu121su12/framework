<?php

namespace Laravel\Octane;

class PosixExtension
{
    /**
     * Send a signal to a given process using the POSIX extension.
     *
     * @param  int  $processId
     * @param  int  $signal
     * @return bool
     */
    public function kill(/*int */$processId, /*int */$signal)
    {
        $processId = cast_to_int($processId);

        $signal = cast_to_int($signal);

        return posix_kill($processId, $signal);
    }
}
