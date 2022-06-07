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
 * Parser and formatter for 12 hour format (1-12).
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 *
 * @internal
 */
class Hour1201Transformer extends HourTransformer
{
    /**
     * {@inheritdoc}
     */
    public function format(\DateTime $dateTime, /*int */$length)/*: string*/
    {
        $length = cast_to_int($length);

        return $this->padLeft($dateTime->format('g'), $length);
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeHour(/*int */$hour, /*string */$marker = null)/*: int*/
    {
        $hour = cast_to_int($hour);

        $marker = cast_to_string($marker, null);

        if ('PM' !== $marker && 12 === $hour) {
            $hour = 0;
        } elseif ('PM' === $marker && 12 !== $hour) {
            // If PM and hour is not 12 (1-12), sum 12 hour
            $hour += 12;
        }

        return $hour;
    }

    /**
     * {@inheritdoc}
     */
    public function getReverseMatchingRegExp(/*int */$length)/*: string*/
    {
        $length = cast_to_int($length);

        return '\d{1,2}';
    }

    /**
     * {@inheritdoc}
     */
    public function extractDateOptions(/*string */$matched, /*int */$length)/*: array*/
    {
        $length = cast_to_int($length);

        $matched = cast_to_string($matched);

        return [
            'hour' => (int) $matched,
            'hourInstance' => $this,
        ];
    }
}
