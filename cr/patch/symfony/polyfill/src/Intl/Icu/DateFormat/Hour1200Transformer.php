<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Intl\Icu\DateFormat;

/**
 * Parser and formatter for 12 hour format (0-11).
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 *
 * @internal
 */
class Hour1200Transformer extends HourTransformer
{
    /**
     * {@inheritdoc}
     */
    public function format(\DateTime $dateTime, /*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        $hourOfDay = $dateTime->format('g');
        $hourOfDay = '12' === $hourOfDay ? '0' : $hourOfDay;

        return $this->padLeft($hourOfDay, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeHour(/*int */$hour, /*string */$marker = null)/*: int*/
    {
        $hour = backport_type_check('int', $hour);

        $marker = backport_type_check('?string', $marker);

        if ('PM' === $marker) {
            $hour += 12;
        }

        return $hour;
    }

    /**
     * {@inheritdoc}
     */
    public function getReverseMatchingRegExp(/*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        return '\d{1,2}';
    }

    /**
     * {@inheritdoc}
     */
    public function extractDateOptions(/*string */$matched, /*int */$length)/*: array*/
    {
        $length = backport_type_check('int', $length);

        $matched = backport_type_check('string', $matched);

        return [
            'hour' => (int) $matched,
            'hourInstance' => $this,
        ];
    }
}
