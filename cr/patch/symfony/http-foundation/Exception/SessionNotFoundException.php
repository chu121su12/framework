<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Exception;

/**
 * Raised when a session does not exists. This happens in the following cases:
 * - the session is not enabled
 * - attempt to read a session outside a request context (ie. cli script).
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class SessionNotFoundException extends \LogicException implements RequestExceptionInterface
{
    public function __construct(/*string */$message = 'There is currently no session available.', /*int */$code = 0, /*\Throwable */$previous = null)
    {
        $message = backport_type_check('string', $message);

        $code = backport_type_check('int', $code);

        backport_type_throwable($previous, null);

        parent::__construct($message, $code, $previous);
    }
}
