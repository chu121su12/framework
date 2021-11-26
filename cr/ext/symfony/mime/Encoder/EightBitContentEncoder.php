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

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class EightBitContentEncoder implements ContentEncoderInterface
{
    public function encodeByteStream($stream, $maxLineLength = 0) //// iterable
    {
        $maxLineLength = cast_to_int($maxLineLength);

        while (!feof($stream)) {
            yield fread($stream, 16372);
        }
    }

    public function getName() //// string
    {
        return '8bit';
    }

    public function encodeString($string, $charset = 'utf-8', $firstLineOffset = 0, $maxLineLength = 0) //// string
    {
        $string = cast_to_string($string);

        $maxLineLength = cast_to_int($maxLineLength);

        $firstLineOffset = cast_to_int($firstLineOffset);

        $charset = cast_to_string($charset, null);

        return $string;
    }
}
