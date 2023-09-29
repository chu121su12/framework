<?php

namespace Laravel\Prompts\Concerns;

use InvalidArgumentException;

trait Truncation
{
    /**
     * Truncate a value with an ellipsis if it exceeds the given width.
     */
    protected function truncate(/*string */$string, /*int */$width)/*: string*/
    {
        $string = backport_type_check('string', $string);
        $width = backport_type_check('int', $width);

        if ($width <= 0) {
            throw new InvalidArgumentException("Width [{$width}] must be greater than zero.");
        }

        return mb_strwidth($string) <= $width ? $string : (mb_strimwidth($string, 0, $width - 1).'…');
    }
}
