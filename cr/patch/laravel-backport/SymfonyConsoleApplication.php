<?php

namespace CR\LaravelBackport;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsoleApplication extends Application
{
    public function renderThrowable($e, OutputInterface $output)
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);

        $this->doRenderThrowable($e, $output);

        if (null !== $this->runningCommand) {
            $output->writeln(sprintf('<info>%s</info>', sprintf($this->runningCommand->getSynopsis(), $this->getName())), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }
    }

    protected function doRenderThrowable($e, OutputInterface $output)
    {
        do {
            $message = trim($e->getMessage());
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $class = get_debug_type($e);
                $title = sprintf('  [%s%s]  ', $class, 0 !== ($code = $e->getCode()) ? ' ('.$code.')' : '');
                $len = SymfonyHelper::consoleStrlen($title);
            } else {
                $len = 0;
            }

            if (false !== strpos($message, "@anonymous\0")) {
                $message = preg_replace_callback('/[a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*+@anonymous\x00.*?\.php(?:0x?|:[0-9]++\$)[0-9a-fA-F]++/', function ($m) {
                    return class_exists($m[0], false) ? (get_parent_class($m[0]) ?: key(class_implements($m[0])) ?: 'class').'@anonymous' : $m[0];
                }, $message);
            }

            $width = $this->getTerminalWidth() ? $this->getTerminalHeight() - 1 : PHP_INT_MAX;
            $lines = [];
            foreach ('' !== $message ? preg_split('/\r?\n/', $message) : [] as $line) {
                foreach ($this->splitStringByWidth($line, $width - 4) as $line) {
                    // pre-format lines to get the right string length
                    $lineLength = SymfonyHelper::consoleStrlen($line) + 4;
                    $lines[] = [$line, $lineLength];

                    $len = max($lineLength, $len);
                }
            }

            $messages = [];
            if (!$e instanceof ExceptionInterface || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = sprintf('<comment>%s</comment>', OutputFormatter::escape(sprintf('In %s line %s:', basename($e->getFile()) ?: 'n/a', $e->getLine() ?: 'n/a')));
            }
            $messages[] = $emptyLine = sprintf('<error>%s</error>', str_repeat(' ', $len));
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = sprintf('<error>%s%s</error>', $title, str_repeat(' ', max(0, $len - SymfonyHelper::consoleStrlen($title))));
            }
            foreach ($lines as $line) {
                $messages[] = sprintf('<error>  %s  %s</error>', OutputFormatter::escape($line[0]), str_repeat(' ', $len - $line[1]));
            }
            $messages[] = $emptyLine;
            $messages[] = '';

            $output->writeln($messages, OutputInterface::VERBOSITY_QUIET);

            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);

                // exception related properties
                $trace = $e->getTrace();

                array_unshift($trace, [
                    'function' => '',
                    'file' => $e->getFile() ?: 'n/a',
                    'line' => $e->getLine() ?: 'n/a',
                    'args' => [],
                ]);

                for ($i = 0, $count = \count($trace); $i < $count; ++$i) {
                    $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
                    $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
                    $function = isset($trace[$i]['function']) ? $trace[$i]['function'] : '';
                    $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
                    $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

                    $output->writeln(sprintf(' %s%s at <info>%s:%s</info>', $class, $function ? $type.$function.'()' : '', $file, $line), OutputInterface::VERBOSITY_QUIET);
                }

                $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            }
        } while ($e = $e->getPrevious());
    }

}
