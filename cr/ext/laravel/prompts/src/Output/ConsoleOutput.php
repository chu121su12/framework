<?php

namespace Laravel\Prompts\Output;

use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;

class ConsoleOutput extends SymfonyConsoleOutput
{
    /**
     * How many new lines were written by the last output.
     */
    protected /*int */$newLinesWritten = 1;

    /**
     * How many new lines were written by the last output.
     */
    public function newLinesWritten()/*: int*/
    {
        return $this->newLinesWritten;
    }

    /**
     * Write the output and capture the number of trailing new lines.
     */
    protected function doWrite(/*string */$message, /*bool */$newline)/*: void*/
    {
        $newline = backport_type_check('bool', $newline);

        $message = backport_type_check('string', $message);

        parent::doWrite($message, $newline);

        if ($newline) {
            $message .= \PHP_EOL;
        }

        $trailingNewLines = strlen($message) - strlen(rtrim($message, \PHP_EOL));

        if (trim($message) === '') {
            $this->newLinesWritten += $trailingNewLines;
        } else {
            $this->newLinesWritten = $trailingNewLines;
        }
    }

    /**
     * Write output directly, bypassing newline capture.
     */
    public function writeDirectly(/*string */$message)/*: void*/
    {
        $message = backport_type_check('string', $message);

        parent::doWrite($message, false);
    }

    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        return parent::write(
            \is_object($messages) && \method_exists($messages, '__toString') ? $messages->__toString() : $messages,
            $newline,
            $options
        );
    }
}
