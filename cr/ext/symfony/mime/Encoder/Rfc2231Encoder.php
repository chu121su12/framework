<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Encoder;

use Symfony\Component\Mime\CharacterStream;

/**
 * @author Chris Corbyn
 */
final class Rfc2231Encoder implements EncoderInterface
{
    /**
     * Takes an unencoded string and produces a string encoded according to RFC 2231 from it.
     */
    public function encodeString($string, $charset = 'utf-8', $firstLineOffset = 0, $maxLineLength = 0) //// string
    {
        $string = backport_type_check('string', $string);

        $maxLineLength = backport_type_check('int', $maxLineLength);

        $firstLineOffset = backport_type_check('int', $firstLineOffset);

        $charset = backport_type_check('?string', $charset);

        $lines = [];
        $lineCount = 0;
        $lines[] = '';
        $currentLine = &$lines[$lineCount++];

        if (0 >= $maxLineLength) {
            $maxLineLength = 75;
        }

        $charStream = new CharacterStream($string, $charset);
        $thisLineLength = $maxLineLength - $firstLineOffset;

        while (null !== $char = $charStream->read(4)) {
            $encodedChar = rawurlencode($char);
            if ('' !== $currentLine && \strlen($currentLine.$encodedChar) > $thisLineLength) {
                $lines[] = '';
                $currentLine = &$lines[$lineCount++];
                $thisLineLength = $maxLineLength;
            }
            $currentLine .= $encodedChar;
        }

        return implode("\r\n", $lines);
    }
}
