<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessFailureStrategy extends Process
{
    public function isSuccessful()/*: bool*/
    {
        return false;
    }

    public function getExitCode()/*: ?int*/
    {
        return 1;
    }

    public function isOutputDisabled()/*: bool*/
    {
        return true;
    }

    public function getWorkingDirectory()/*: ?string*/
    {
        return 'expected-working-directory';
    }
}

return function () {
    throw new ProcessFailedException(new ProcessFailureStrategy(['expected-command']));
};
