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
        $processId = backport_type_check('int', $processId);

        $signal = backport_type_check('int', $signal);

        return posix_kill($processId, $signal);
    }
}
