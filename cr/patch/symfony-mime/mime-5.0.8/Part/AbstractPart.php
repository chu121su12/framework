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

use Symfony\Component\Mime\Header\Headers;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractPart
{
    private $headers;

    public function __construct()
    {
        $this->headers = new Headers();
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getPreparedHeaders()
    {
        $headers = clone $this->headers;
        $headers->setHeaderBody('Parameterized', 'Content-Type', $this->getMediaType().'/'.$this->getMediaSubtype());

        return $headers;
    }

    public function toString()
    {
        return $this->getPreparedHeaders()->toString()."\r\n".$this->bodyToString();
    }

    public function toIterable()
    {
        yield $this->getPreparedHeaders()->toString();
        yield "\r\n";
        $bodyIterable = $part->bodyToIterable();
        foreach ($bodyIterable as $yieldedKey => $yieldedValue) {
            yield $yieldedKey => $yieldedValue;
        }
    }

    public function asDebugString()
    {
        return $this->getMediaType().'/'.$this->getMediaSubtype();
    }

    abstract public function bodyToString();

    abstract public function bodyToIterable();

    abstract public function getMediaType();

    abstract public function getMediaSubtype();
}
