<?php

namespace Laravel\Octane;

use Symfony\Component\Process\Process;

class SymfonyProcessFactory
{
    /**
     * Create a new Symfony process instance.
     *
     * @param  string  $cwd
     * @param  mixed|null  $input
     * @param  float|null  $timeout
     * @return \Symfony\Component\Process\Process
     */
    public function createProcess(array $command, /*?string */$cwd = null, /*?*/array $env = null, $input = null, /*?float */$timeout = 60)
    {
        $timeout = backport_type_check('?float', $timeout);

        $cwd = backport_type_check('?string', $cwd);

        return new Process($command, $cwd, $env, $input, $timeout);
    }
}
