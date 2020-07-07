<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String;

/**
 * @experimental in 5.0
 */
function u($string = '')
{
    return new UnicodeString($string);
}

/**
 * @experimental in 5.0
 */
function b($string = '')
{
    return new ByteString($string);
}
