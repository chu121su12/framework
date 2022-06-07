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
 * Parser and formatter for year format.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 *
 * @internal
 */
class YearTransformer extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function format(\DateTime $dateTime, /*int */$length)/*: string*/
    {
        $length = cast_to_int($length);

        if (2 === $length) {
            return $dateTime->format('y');
        }

        return $this->padLeft($dateTime->format('Y'), $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getReverseMatchingRegExp(/*int */$length)/*: string*/
    {
        $length = cast_to_int($length);

        return 2 === $length ? '\d{2}' : '\d{1,4}';
    }

    /**
     * {@inheritdoc}
     */
    public function extractDateOptions(/*string */$matched, /*int */$length)/*: array*/
    {
        $length = cast_to_int($length);

        $matched = cast_to_string($matched);

        return [
            'year' => (int) $matched,
        ];
    }
}
