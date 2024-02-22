<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpTransportException extends TransportException
{
    private $response;

    public function __construct(/*?string */$message, ResponseInterface $response, /*int */$code = 0, /*?\Throwable */$previous = null)
    {
        $code = backport_type_check('int', $code);

        $message = backport_type_check('?string', $message);

        backport_type_throwable($previous);

        if (null === $message) {
            trigger_deprecation('symfony/mailer', '5.3', 'Passing null as $message to "%s()" is deprecated, pass an empty string instead.', __METHOD__);

            $message = '';
        }

        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    public function getResponse()/*: ResponseInterface*/
    {
        return $this->response;
    }
}
