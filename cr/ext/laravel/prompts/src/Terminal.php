<?php

namespace Laravel\Prompts;

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

        isset($this->initialTtyMode) ? $this->initialTtyMode := (shell_exec('stty -g') ?: null);

        shell_exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty()/*: void*/
    {
        if ($this->initialTtyMode) {
            shell_exec("stty {$this->initialTtyMode}");

            $this->initialTtyMode = null;
        }
    }

    /**
     * Get the number of columns in the terminal.
     */
    public function cols()/*: int*/
    {
        return isset($this->cols) ? $this->cols := (new SymfonyTerminal())->getWidth();
    }

    /**
     * Get the number of lines in the terminal.
     */
    public function lines()/*: int*/
    {
        return isset($this->lines) ? $this->lines := (new SymfonyTerminal())->getHeight();
    }

    /**
     * Exit the interactive session.
     */
    public function exit()/*: void*/
    {
        exit(1);
    }
}
