<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Transport\Smtp\Stream;

use Symfony\Component\Mailer\Exception\TransportException;

/**
 * A stream supporting remote sockets.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Chris Corbyn
 *
 * @internal
 */
final class SocketStream extends AbstractStream
{
    private $url;
    private $host = 'localhost';
    private $port = 465;
    private $timeout;
    private $tls = true;
    private $sourceIp;
    private $streamContextOptions = [];

    /**
     * @return $this
     */
    public function setTimeout(/*float */$timeout)/*: self*/
    {
        $timeout = backport_type_check('float', $timeout);

        $this->timeout = $timeout;

        return $this;
    }

    public function getTimeout()/*: float*/
    {
        return isset($this->timeout) ? $this->timeout : (float) \ini_get('default_socket_timeout');
    }

    /**
     * Literal IPv6 addresses should be wrapped in square brackets.
     *
     * @return $this
     */
    public function setHost(/*string */$host)/*: self*/
    {
        $host = backport_type_check('string', $host);

        $this->host = $host;

        return $this;
    }

    public function getHost()/*: string*/
    {
        return $this->host;
    }

    /**
     * @return $this
     */
    public function setPort(/*int */$port)/*: self*/
    {
        $port = backport_type_check('int', $port);

        $this->port = $port;

        return $this;
    }

    public function getPort()/*: int*/
    {
        return $this->port;
    }

    /**
     * Sets the TLS/SSL on the socket (disables STARTTLS).
     *
     * @return $this
     */
    public function disableTls()/*: self*/
    {
        $this->tls = false;

        return $this;
    }

    public function isTLS()/*: bool*/
    {
        return $this->tls;
    }

    /**
     * @return $this
     */
    public function setStreamOptions(array $options)/*: self*/
    {
        $this->streamContextOptions = $options;

        return $this;
    }

    public function getStreamOptions()/*: array*/
    {
        return $this->streamContextOptions;
    }

    /**
     * Sets the source IP.
     *
     * IPv6 addresses should be wrapped in square brackets.
     *
     * @return $this
     */
    public function setSourceIp(/*string */$ip)/*: self*/
    {
        $ip = backport_type_check('string', $ip);

        $this->sourceIp = $ip;

        return $this;
    }

    /**
     * Returns the IP used to connect to the destination.
     */
    public function getSourceIp()/*: ?string*/
    {
        return $this->sourceIp;
    }

    public function initialize()/*: void*/
    {
        $this->url = $this->host.':'.$this->port;
        if ($this->tls) {
            $this->url = 'ssl://'.$this->url;
        }
        $options = [];
        if ($this->sourceIp) {
            $options['socket']['bindto'] = $this->sourceIp.':0';
        }
        if ($this->streamContextOptions) {
            $options = array_merge($options, $this->streamContextOptions);
        }
        // do it unconditionally as it will be used by STARTTLS as well if supported
        $options['ssl']['crypto_method'] = isset($options['ssl']) && isset($options['ssl']['crypto_method']) ? $options['ssl']['crypto_method'] : \STREAM_CRYPTO_METHOD_TLS_CLIENT | \STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | \STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        $streamContext = stream_context_create($options);

        $timeout = $this->getTimeout();
        set_error_handler(function ($type, $msg) {
            throw new TransportException(sprintf('Connection could not be established with host "%s": ', $this->url).$msg);
        });
        try {
            $this->stream = stream_socket_client($this->url, $errno, $errstr, $timeout, \STREAM_CLIENT_CONNECT, $streamContext);
        } finally {
            restore_error_handler();
        }

        stream_set_blocking($this->stream, true);
        stream_set_timeout($this->stream, $timeout);
        $this->in = &$this->stream;
        $this->out = &$this->stream;
    }

    public function startTLS()/*: bool*/
    {
        set_error_handler(function ($type, $msg) {
            throw new TransportException('Unable to connect with STARTTLS: '.$msg);
        });
        try {
            return stream_socket_enable_crypto($this->stream, true);
        } finally {
            restore_error_handler();
        }
    }

    public function terminate()/*: void*/
    {
        if (null !== $this->stream) {
            fclose($this->stream);
        }

        parent::terminate();
    }

    protected function getReadConnectionDescription()/*: string*/
    {
        return $this->url;
    }
}
