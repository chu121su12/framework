<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String\Exception;

/**
 * @experimental in 5.0
 */
if (interface_exists('Throwable'))
{
    interface ExceptionInterface extends \Throwable
    {
    }
}
else
{
    interface ExceptionInterface
    {
    }
}
