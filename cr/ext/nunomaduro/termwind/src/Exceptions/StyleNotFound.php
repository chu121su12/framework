<?php

/*declare(strict_types=1);*/

namespace Termwind\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class StyleNotFound extends InvalidArgumentException
{
    /**
     * Creates a new style not found instance.
     */
    /*private */
    final public function __construct(/*string */$message)
    {
        $message = backport_type_check('string', $message);

        parent::__construct($message, 0, $this->getPrevious());
    }

    /**
     * Creates a new style not found instance from the given style.
     */
    public static function fromStyle(/*string */$style)/*: self*/
    {
        $style = backport_type_check('string', $style);

        return new self(sprintf('Style [%s] not found.', $style));
    }
}
