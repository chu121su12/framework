<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Allows to manipulate the exit code of a command after its execution.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
final class ConsoleTerminateEvent extends ConsoleEvent
{
    private $exitCode;

    public function __construct(Command $command, InputInterface $input, OutputInterface $output, $exitCode)
    {
        $exitCode = cast_to_int($exitCode);

        parent::__construct($command, $input, $output);

        $this->setExitCode($exitCode);
    }

    public function setExitCode($exitCode)
    {
        $exitCode = cast_to_int($exitCode);

        $this->exitCode = $exitCode;
    }

    public function getExitCode()
    {
        return $this->exitCode;
    }
}