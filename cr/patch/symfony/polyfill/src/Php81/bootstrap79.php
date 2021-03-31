<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Polyfill\Php81 as p;

if (!function_exists('array_is_list')) {
    function array_is_list($array) { return p\Php81::array_is_list($array); }
}
