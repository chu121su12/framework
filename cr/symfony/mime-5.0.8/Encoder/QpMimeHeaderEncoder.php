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
 * @author Chris Corbyn
 */
final class QpMimeHeaderEncoder extends QpEncoder implements MimeHeaderEncoderInterface
{
    protected function initSafeMap()
    {
        foreach (array_merge(
            range(0x61, 0x7A), range(0x41, 0x5A),
            range(0x30, 0x39), [0x20, 0x21, 0x2A, 0x2B, 0x2D, 0x2F]
        ) as $byte) {
            $this->safeMap[$byte] = \chr($byte);
        }
    }

    public function getName()
    {
        return 'Q';
    }

    public function encodeString($string, $charset = 'utf-8', $firstLineOffset = 0, $maxLineLength = 0)
    {
        $string = cast_to_string($string);

        $maxLineLength = cast_to_int($maxLineLength);

        $firstLineOffset = cast_to_int($firstLineOffset);

        $charset = cast_to_string($charset, null);

        return str_replace([' ', '=20', "=\r\n"], ['_', '_', "\r\n"],
            parent::encodeString($string, $charset, $firstLineOffset, $maxLineLength)
        );
    }
}
