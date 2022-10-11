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
 * Parser and formatter for the second format.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 *
 * @internal
 */
class SecondTransformer extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function format(\DateTime $dateTime, /*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        $secondOfMinute = (int) $dateTime->format('s');

        return $this->padLeft($secondOfMinute, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getReverseMatchingRegExp(/*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        return 1 === $length ? '\d{1,2}' : '\d{'.$length.'}';
    }

    /**
     * {@inheritdoc}
     */
    public function extractDateOptions(/*string */$matched, /*int */$length)/*: array*/
    {
        $length = backport_type_check('int', $length);

        $matched = backport_type_check('string', $matched);

        return [
            'second' => (int) $matched,
        ];
    }
}
