<?php

namespace Illuminate\Console\Process;

use Illuminate\Console\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Contracts\Console\Process\InvokedProcess as InvokedProcessContract;
use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyTimeoutException;
use Symfony\Component\Process\Process;

class InvokedProcess implements InvokedProcessContract
{
    /**
     * The underlying process instance.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * Create a new invoked process instance.
     *
     * @param  \Symfony\Component\Process\Process  $process
     * @return void
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Get the process ID if the process is still running.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->process->getPid();
    }

    /**
     * Send a signal to the process.
     *
     * @param  int  $signal
     * @return $this
     */
    public function signal(/*int */$signal)
    {
        $signal = backport_type_check('int', $signal);

        $this->process->signal($signal);

        return $this;
    }

    /**
     * Determine if the process is still running.
     *
     * @return bool
     */
    public function running()
    {
        return $this->process->isRunning();
    }

    /**
     * Get the standard output for the process.
     *
     * @return string
     */
    public function output()
    {
        return $this->process->getOutput();
    }

    /**
     * Get the error output for the process.
     *
     * @return string
     */
    public function errorOutput()
    {
        return $this->process->getErrorOutput();
    }

    /**
     * Get the latest standard output for the process.
     *
     * @return string
     */
    public function latestOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    /**
     * Get the latest error output for the process.
     *
     * @return string
     */
    public function latestErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }

    /**
     * Wait for the process to finish.
     *
     * @param  callable|null  $output
     * @return \Illuminate\Console\Process\ProcessResult
     */
    public function wait(callable $output = null)
    {
        try {
            $this->process->wait($output);

            return new ProcessResult($this->process);
        } catch (SymfonyTimeoutException $e) {
            throw new ProcessTimedOutException($e, new ProcessResult($this->process));
        }
    }
}
