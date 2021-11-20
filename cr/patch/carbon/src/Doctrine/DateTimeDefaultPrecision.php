<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Doctrine;

class DateTimeDefaultPrecision
{
    private static $precision = 6;

    /**
     * Change the default Doctrine datetime and datetime_immutable precision.
     *
     * @param int $precision
     */
    public static function set($precision) /// void
    {
        $precision = cast_to_int($precision);

        self::$precision = $precision;
    }

    /**
     * Get the default Doctrine datetime and datetime_immutable precision.
     *
     * @return int
     */
    public static function get() //// int
    {
        return self::$precision;
    }
}
