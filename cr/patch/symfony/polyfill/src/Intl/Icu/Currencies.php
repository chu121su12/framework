<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Intl\Icu;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
class Currencies
{
    private static $data;

    public static function getSymbol(/*string */$currency)/*: ?string*/
    {
        $currency = cast_to_string($currency);

        $data = isset(self::$data) ? self::$data : self::$data = require __DIR__.'/Resources/currencies.php';

        $currencyUppercase = strtoupper($currency);

        return isset($data[$currency]) && isset($data[$currency][0]) ? $data[$currency][0] : (isset($data[$currencyUppercase]) && isset($data[$currencyUppercase][0]) ? $data[$currencyUppercase][0] : null);
    }

    public static function getFractionDigits(/*string */$currency)/*: int*/
    {
        $currency = cast_to_string($currency);

        $data = isset(self::$data) ? self::$data : self::$data = require __DIR__.'/Resources/currencies.php';

        $currencyUppercase = strtoupper($currency);

        return isset($data[$currency]) && isset($data[$currency][1]) ? $data[$currency][1] : (isset($data[$currencyUppercase]) && isset($data[$currencyUppercase][1]) ? $data[$currencyUppercase][1] : $data['DEFAULT'][1]);
    }

    public static function getRoundingIncrement(/*string */$currency)/*: int*/
    {
        $currency = cast_to_string($currency);

        $data = isset(self::$data) ? self::$data : self::$data = require __DIR__.'/Resources/currencies.php';

        $currencyUppercase = strtoupper($currency);

        return isset($data[$currency]) && isset($data[$currency][2]) ? $data[$currency][2] : (isset($data[$currencyUppercase]) && isset($data[$currencyUppercase][2]) ? $data[$currencyUppercase][2] : $data['DEFAULT'][2]);
    }
}
