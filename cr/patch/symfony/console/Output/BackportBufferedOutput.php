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

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class BackportBufferedOutput extends BufferedOutput
{
    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;

        foreach ($messages as $key => $message) {
            $messages[$key] = SymfonyHelper::consoleOutputMessage($message, false);
        }

        return parent::write($messages, $newline, $options);
    }
}
