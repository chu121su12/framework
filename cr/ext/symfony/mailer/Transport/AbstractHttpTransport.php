<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Transport;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Victor Bocharsky <victor@symfonycasts.com>
 */
abstract class AbstractHttpTransport extends AbstractTransport
{
    protected $host;
    protected $port;
    protected $client;

    public function __construct(/*?*/HttpClientInterface $client = null, /*?*/EventDispatcherInterface $dispatcher = null, /*?*/LoggerInterface $logger = null)
    {
        $this->client = $client;
        if (null === $client) {
            if (!class_exists(HttpClient::class)) {
                throw new \LogicException(sprintf('You cannot use "%s" as the HttpClient component is not installed. Try running "composer require symfony/http-client".', __CLASS__));
            }

            $this->client = HttpClient::create();
        }

        parent::__construct($dispatcher, $logger);
    }

    /**
     * @return $this
     */
    public function setHost(?string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return $this
     */
    public function setPort(?int $port)
    {
        $this->port = $port;

        return $this;
    }

    abstract protected function doSendHttp(SentMessage $message): ResponseInterface;

    protected function doSend(SentMessage $message): void
    {
        $response = null;
        try {
            $response = $this->doSendHttp($message);
            $debugInfo = $response->getInfo('debug');
            $message->appendDebug(isset($debugInfo) ? $debugInfo : '');
        } catch (HttpTransportException $e) {
            $debugInfo = $e->getResponse()->getInfo('debug');
            $e->appendDebug(isset($debugInfo) ? $debugInfo : '');

            throw $e;
        }
    }
}
