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

if (\PHP_VERSION_ID >= 80100) {
    return;
}

if (\PHP_VERSION_ID >= 80000) {
    return require __DIR__.'/bootstrap80.php';
}

if (defined('MYSQLI_REFRESH_SLAVE') && !defined('MYSQLI_REFRESH_REPLICA')) {
    define('MYSQLI_REFRESH_REPLICA', 64);
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $array) { return p\Php81::array_is_list($array); }
}

if (!function_exists('enum_exists')) {
    function enum_exists($enum, $autoload = true) { return backport_type_check('bool', $autoload) && class_exists(backport_type_check('string', $enum)) && false; }
}
