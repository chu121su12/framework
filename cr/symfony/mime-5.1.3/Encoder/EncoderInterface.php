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
interface EncoderInterface
{
    /**
     * Encode a given string to produce an encoded string.
     *
     * @param int $firstLineOffset if first line needs to be shorter
     * @param int $maxLineLength   - 0 indicates the default length for this encoding
     */
    public function encodeString($string, $charset = 'utf-8', $firstLineOffset = 0, $maxLineLength = 0); //// string
}
