<?php

/*declare(strict_types=1);*/

namespace Brick\Math\Exception;

/**
 * Exception thrown when attempting to create a number from a string with an invalid format.
 */
class NumberFormatException extends MathException
{
    public static function invalidFormat(/*string */$value)/* : self*/
    {
        $value = backport_type_check('string', $value);

        return new self(\sprintf(
            'The given value "%s" does not represent a valid number.',
            $value
        ));
    }

    /**
     * @param string $char The failing character.
     *
     * @return NumberFormatException
     *
     * @psalm-pure
     */
    public static function charNotInAlphabet(/*string */$char)/* : self*/
    {
        $char = backport_type_check('string', $char);

        $ord = \ord($char);

        if ($ord < 32 || $ord > 126) {
            $char = \strtoupper(\dechex($ord));

            if ($ord < 10) {
                $char = '0' . $char;
            }
        } else {
            $char = '"' . $char . '"';
        }

        return new self(sprintf('Char %s is not a valid character in the given alphabet.', $char));
    }
}
