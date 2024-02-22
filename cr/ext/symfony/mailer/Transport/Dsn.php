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

use Symfony\Component\Mailer\Exception\InvalidArgumentException;

/**
 * @author Konstantin Myakshin <molodchick@gmail.com>
 */
final class Dsn
{
    private $scheme;
    private $host;
    private $user;
    private $password;
    private $port;
    private $options;

    public function __construct(/*string */$scheme, /*string */$host, /*?string */$user = null, /*?string */$password = null, /*?int */$port = null, array $options = [])
    {
        $port = backport_type_check('?int', $port);

        $password = backport_type_check('?string', $password);

        $user = backport_type_check('?string', $user);

        $host = backport_type_check('string', $host);

        $scheme = backport_type_check('string', $scheme);

        $this->scheme = $scheme;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->options = $options;
    }

    public static function fromString(/*string */$dsn)/*: self*/
    {
        $dsn = backport_type_check('string', $dsn);

        if (false === $params = parse_url($dsn)) {
            throw new InvalidArgumentException('The mailer DSN is invalid.');
        }

        if (!isset($params['scheme'])) {
            throw new InvalidArgumentException('The mailer DSN must contain a scheme.');
        }

        if (!isset($params['host'])) {
            throw new InvalidArgumentException('The mailer DSN must contain a host (use "default" by default).');
        }

        $user = '' !== (isset($params['user']) ? $params['user'] : '') ? rawurldecode($params['user']) : null;
        $password = '' !== (isset($params['pass']) ? $params['pass'] : '') ? rawurldecode($params['pass']) : null;
        $port = isset($params['port']) ? $params['port'] : null;
        parse_str(isset($params['query']) ? $params['query'] : '', $query);

        return new self($params['scheme'], $params['host'], $user, $password, $port, $query);
    }

    public function getScheme()/*: string*/
    {
        return $this->scheme;
    }

    public function getHost()/*: string*/
    {
        return $this->host;
    }

    public function getUser()/*: ?string*/
    {
        return $this->user;
    }

    public function getPassword()/*: ?string*/
    {
        return $this->password;
    }

    public function getPort(/*?int */$default = null)/*: ?int*/
    {
        $default = backport_type_check('?int', $default);

        return isset($this->port) ? $this->port : $default;
    }

    public function getOption(/*string*/$key, $default = null)
    {
        $key = backport_type_check('string', $key);

        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
}
