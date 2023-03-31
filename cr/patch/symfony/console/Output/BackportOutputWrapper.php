<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Output;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class BackportOutputWrapper implements OutputInterface
{
    private $implementation;

    private function __construct(OutputInterface $implementation)
    {
        $this->implementation = $implementation;
    }

    public static function wrap(OutputInterface $impl)
    {
        return $impl instanceof static ? $impl : new static($impl);
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->implementation->setFormatter($formatter);
    }

    public function getFormatter()
    {
        return $this->implementation->getFormatter();
    }

    public function setDecorated($decorated)
    {
        $this->implementation->setDecorated($decorated);
    }

    public function isDecorated()
    {
        return $this->implementation->isDecorated();
    }

    public function setVerbosity($level)
    {
        $this->implementation->setVerbosity($level);
    }

    public function getVerbosity()
    {
        return $this->implementation->getVerbosity();
    }

    public function isQuiet()
    {
        return $this->implementation->isQuiet();
    }

    public function isVerbose()
    {
        return $this->implementation->isVerbose();
    }

    public function isVeryVerbose()
    {
        return $this->implementation->isVeryVerbose();
    }

    public function isDebug()
    {
        return $this->implementation->isVeryVerbose();
    }

    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        $this->write($messages, true, $options);
    }

    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;

        foreach ($messages as $key => $message) {
            $messages[$key] = SymfonyHelper::consoleOutputMessage($message, false);
        }

        $this->implementation->write($messages, $newline, $options);
    }
}
