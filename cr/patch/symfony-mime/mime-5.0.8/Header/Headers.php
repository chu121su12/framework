<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;

/**
 * A collection of headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Headers
{
    private static $uniqueHeaders = [
        'date', 'from', 'sender', 'reply-to', 'to', 'cc', 'bcc',
        'message-id', 'in-reply-to', 'references', 'subject',
    ];

    private $headers = [];
    private $lineLength = 76;

    public function __construct(HeaderInterface ...$headers)
    {
        foreach ($headers as $header) {
            $this->add($header);
        }
    }

    public function __clone()
    {
        foreach ($this->headers as $name => $collection) {
            foreach ($collection as $i => $header) {
                $this->headers[$name][$i] = clone $header;
            }
        }
    }

    public function setMaxLineLength($lineLength)
    {
        $this->lineLength = $lineLength;
        foreach ($this->all() as $header) {
            $header->setMaxLineLength($lineLength);
        }
    }

    public function getMaxLineLength()
    {
        return $this->lineLength;
    }

    /**
     * @param (Address|string)[] $addresses
     *
     * @return $this
     */
    public function addMailboxListHeader($name, array $addresses)
    {
        return $this->add(new MailboxListHeader($name, Address::createArray($addresses)));
    }

    /**
     * @param Address|string $address
     *
     * @return $this
     */
    public function addMailboxHeader($name, $address)
    {
        return $this->add(new MailboxHeader($name, Address::create($address)));
    }

    /**
     * @param string|array $ids
     *
     * @return $this
     */
    public function addIdHeader($name, $ids)
    {
        return $this->add(new IdentificationHeader($name, $ids));
    }

    /**
     * @param Address|string $path
     *
     * @return $this
     */
    public function addPathHeader($name, $path)
    {
        return $this->add(new PathHeader($name, $path instanceof Address ? $path : new Address($path)));
    }

    /**
     * @return $this
     */
    public function addDateHeader($name, \DateTimeInterface $dateTime)
    {
        return $this->add(new DateHeader($name, $dateTime));
    }

    /**
     * @return $this
     */
    public function addTextHeader($name, $value)
    {
        return $this->add(new UnstructuredHeader($name, $value));
    }

    /**
     * @return $this
     */
    public function addParameterizedHeader($name, $value, array $params = [])
    {
        return $this->add(new ParameterizedHeader($name, $value, $params));
    }

    public function has($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @return $this
     */
    public function add(HeaderInterface $header)
    {
        static $map = [
            'date' => DateHeader::class,
            'from' => MailboxListHeader::class,
            'sender' => MailboxHeader::class,
            'reply-to' => MailboxListHeader::class,
            'to' => MailboxListHeader::class,
            'cc' => MailboxListHeader::class,
            'bcc' => MailboxListHeader::class,
            'message-id' => IdentificationHeader::class,
            'in-reply-to' => IdentificationHeader::class,
            'references' => IdentificationHeader::class,
            'return-path' => PathHeader::class,
        ];

        $header->setMaxLineLength($this->lineLength);
        $name = strtolower($header->getName());

        if (isset($map[$name]) && !$header instanceof $map[$name]) {
            throw new LogicException(sprintf('The "%s" header must be an instance of "%s" (got "%s").', $header->getName(), $map[$name], \get_class($header)));
        }

        if (\in_array($name, self::$uniqueHeaders, true) && isset($this->headers[$name]) && \count($this->headers[$name]) > 0) {
            throw new LogicException(sprintf('Impossible to set header "%s" as it\'s already defined and must be unique.', $header->getName()));
        }

        $this->headers[$name][] = $header;

        return $this;
    }

    public function get($name)
    {
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            return null;
        }

        $values = array_values($this->headers[$name]);

        return array_shift($values);
    }

    public function all($name = null)
    {
        if (null === $name) {
            foreach ($this->headers as $name => $collection) {
                foreach ($collection as $header) {
                    yield $name => $header;
                }
            }
        } elseif (isset($this->headers[strtolower($name)])) {
            foreach ($this->headers[strtolower($name)] as $header) {
                yield $header;
            }
        }
    }

    public function getNames()
    {
        return array_keys($this->headers);
    }

    public function remove($name)
    {
        unset($this->headers[strtolower($name)]);
    }

    public static function isUniqueHeader($name)
    {
        return \in_array($name, self::$uniqueHeaders, true);
    }

    public function toString()
    {
        $string = '';
        foreach ($this->toArray() as $str) {
            $string .= $str."\r\n";
        }

        return $string;
    }

    public function toArray()
    {
        $arr = [];
        foreach ($this->all() as $header) {
            if ('' !== $header->getBodyAsString()) {
                $arr[] = $header->toString();
            }
        }

        return $arr;
    }

    /**
     * @internal
     */
    public function getHeaderBody($name)
    {
        return $this->has($name) ? $this->get($name)->getBody();
    }

    /**
     * @internal
     */
    public function setHeaderBody($type, $name, $body)
    {
        if ($this->has($name)) {
            $this->get($name)->setBody($body);
        } else {
            $this->{'add'.$type.'Header'}($name, $body);
        }
    }

    /**
     * @internal
     */
    public function getHeaderParameter($name, $parameter)
    {
        if (!$this->has($name)) {
            return null;
        }

        $header = $this->get($name);
        if (!$header instanceof ParameterizedHeader) {
            throw new LogicException(sprintf('Unable to get parameter "%s" on header "%s" as the header is not of class "%s".', $parameter, $name, ParameterizedHeader::class));
        }

        return $header->getParameter($parameter);
    }

    /**
     * @internal
     */
    public function setHeaderParameter($name, $parameter, $value)
    {
        if (!$this->has($name)) {
            throw new LogicException(sprintf('Unable to set parameter "%s" on header "%s" as the header is not defined.', $parameter, $name));
        }

        $header = $this->get($name);
        if (!$header instanceof ParameterizedHeader) {
            throw new LogicException(sprintf('Unable to set parameter "%s" on header "%s" as the header is not of class "%s".', $parameter, $name, ParameterizedHeader::class));
        }

        $header->setParameter($parameter, $value);
    }
}
