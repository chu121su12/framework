<?php

namespace Illuminate\Console;

use CR\LaravelBackport\SymfonyHelper;
use Illuminate\Console\Contracts\NewLineAware;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OutputStyle extends SymfonyStyle implements NewLineAware
{
    /**
     * The output instance.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * If the last output written wrote a new line.
     *
     * @var bool
     */
    protected $newLineWritten = false;

    /**
     * Create a new Console OutputStyle instance.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        parent::__construct($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function write(/*string|iterable */$messages, /*bool */$newline = false, /*int */$options = 0)
    {
        $messages = cast_to_compound_iterable_string($messages);

        $newline = cast_to_bool($newline);

        $options = cast_to_int($options);

        $this->newLineWritten = $newline;

        parent::write(
            SymfonyHelper::consoleOutputStyle($messages, $this->output),
            $newline,
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function writeln(/*string|iterable */$messages, /*int */$type = self::OUTPUT_NORMAL)
    {
        $messages = cast_to_compound_iterable_string($messages);

        $type = cast_to_int($type);

        $this->newLineWritten = true;

        parent::writeln(
            SymfonyHelper::consoleOutputStyle($messages, $this->output),
            $type
        );
    }

    /**
     * {@inheritdoc}
     */
    public function newLine(/*int */$count = 1)
    {
        $count = cast_to_int($count);

        $this->newLineWritten = $count > 0;

        parent::newLine($count);
    }

    /**
     * {@inheritdoc}
     */
    public function newLineWritten()
    {
        if ($this->output instanceof static && $this->output->newLineWritten()) {
            return true;
        }

        return $this->newLineWritten;
    }

    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool
     */
    public function isQuiet()/*: bool*/
    {
        return $this->output->isQuiet();
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool
     */
    public function isVerbose()/*: bool*/
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool
     */
    public function isVeryVerbose()/*: bool*/
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool
     */
    public function isDebug()/*: bool*/
    {
        return $this->output->isDebug();
    }

    /**
     * Get the underlying Symfony output implementation.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}
