<?php

namespace Laravel\Prompts;

use RuntimeException;
use Symfony\Component\Console\Terminal as SymfonyTerminal;

class Terminal
{
    /**
     * The initial TTY mode.
     */
    protected /*?string */$initialTtyMode;

    /**
     * The number of columns in the terminal.
     */
    protected /*int */$cols;

    /**
     * The number of lines in the terminal.
     */
    protected /*int */$lines;

    /**
     * Read a line from the terminal.
     */
    public function read()/*: string*/
    {
        $input = fread(STDIN, 1024);

        return $input !== false ? $input : '';
    }

    /**
     * Set the TTY mode.
     */
    public function setTty(/*string */$mode)/*: void*/
    {
        $mode = backport_type_check('string', $mode);

        if (! isset($this->initialTtyMode)) {
            $this->initialTtyMode = $this->exec('stty -g');
        }

        $this->exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty()/*: void*/
    {
        if (isset($this->initialTtyMode)) {
            $this->exec("stty {$this->initialTtyMode}");

            $this->initialTtyMode = null;
        }
    }

    /**
     * Get the number of columns in the terminal.
     */
    public function cols()/*: int*/
    {
        if (! isset($this->cols)) {
            $this->cols = (new SymfonyTerminal())->getWidth();
        }

        return $this->cols;
    }

    /**
     * Get the number of lines in the terminal.
     */
    public function lines()/*: int*/
    {
        if (! isset($this->lines)) {
            $this->lines = (new SymfonyTerminal())->getHeight();
        }

        return $this->lines;
    }

    /**
     * Exit the interactive session.
     */
    public function exit_()/*: void*/
    {
        exit(1);
    }

    /**
     * Execute the given command and return the output.
     */
    protected function exec(/*string */$command)/*: string*/
    {
        $command = backport_type_check('string', $command);

        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! $process) {
            throw new RuntimeException('Failed to create process.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0 || $stdout === false) {
            throw new RuntimeException(trim($stderr ?: "Unknown error (code: $code)"), $code);
        }

        return $stdout;
    }
}
