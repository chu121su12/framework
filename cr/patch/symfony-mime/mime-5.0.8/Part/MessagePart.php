<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MessagePart extends DataPart
{
    private $message;

    public function __construct(RawMessage $message)
    {
        if ($message instanceof Message) {
            $name = $message->getHeaders()->getHeaderBody('Subject').'.eml';
        } else {
            $name = 'email.eml';
        }
        parent::__construct('', $name);

        $this->message = $message;
    }

    public function getMediaType()
    {
        return 'message';
    }

    public function getMediaSubtype()
    {
        return 'rfc822';
    }

    public function getBody()
    {
        return $this->message->toString();
    }

    public function bodyToString()
    {
        return $this->getBody();
    }

    public function bodyToIterable()
    {
        return $this->message->toIterable();
    }
}
