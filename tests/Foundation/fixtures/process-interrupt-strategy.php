<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessInterruptStrategy extends Process
{
    public function isSuccessful()/*: bool*/
    {
        return false;
    }

    public function getExitCode()/*: ?int*/
    {
        return 130;
    }

    public function isOutputDisabled()/*: bool*/
    {
        return true;
    }
}

return function () {
    throw new ProcessFailedException(new ProcessInterruptStrategy([]));
};
