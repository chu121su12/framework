<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * Holds information about the current request.
 *
 * This class implements a fluent interface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class RequestContext
{
    private $baseUrl;
    private $pathInfo;
    private $method;
    private $host;
    private $scheme;
    private $httpPort;
    private $httpsPort;
    private $queryString;
    private $parameters = [];

    public function __construct($baseUrl = '', $method = 'GET', $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443, $path = '/', $queryString = '')
    {
        $queryString = backport_type_check('string', $queryString);

        $path = backport_type_check('string', $path);

        $httpsPort = backport_type_check('int', $httpsPort);

        $httpPort = backport_type_check('int', $httpPort);

        $scheme = backport_type_check('string', $scheme);

        $host = backport_type_check('string', $host);

        $method = backport_type_check('string', $method);

        $baseUrl = backport_type_check('string', $baseUrl);

        $this->setBaseUrl($baseUrl);
        $this->setMethod($method);
        $this->setHost($host);
        $this->setScheme($scheme);
        $this->setHttpPort($httpPort);
        $this->setHttpsPort($httpsPort);
        $this->setPathInfo($path);
        $this->setQueryString($queryString);
    }

    public static function fromUri($uri, $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443) /// self
    {
        $uri = backport_type_check('string', $uri);

        $httpsPort = backport_type_check('int', $httpsPort);

        $httpPort = backport_type_check('int', $httpPort);

        $scheme = backport_type_check('string', $scheme);

        $host = backport_type_check('string', $host);

        $uri = parse_url($uri);
        $scheme = isset($uri['scheme']) ? $uri['scheme'] : $scheme;
        $host = isset($uri['host']) ? $uri['host'] : $host;

        if (isset($uri['port'])) {
            if ('http' === $scheme) {
                $httpPort = $uri['port'];
            } elseif ('https' === $scheme) {
                $httpsPort = $uri['port'];
            }
        }

        return new self(isset($uri['path']) ? $uri['path'] : '', 'GET', $host, $scheme, $httpPort, $httpsPort);
    }

    /**
     * Updates the RequestContext information based on a HttpFoundation Request.
     *
     * @return $this
     */
    public function fromRequest(Request $request)
    {
        $this->setBaseUrl($request->getBaseUrl());
        $this->setPathInfo($request->getPathInfo());
        $this->setMethod($request->getMethod());
        $this->setHost($request->getHost());
        $this->setScheme($request->getScheme());
        $this->setHttpPort($request->isSecure() || null === $request->getPort() ? $this->httpPort : $request->getPort());
        $this->setHttpsPort($request->isSecure() && null !== $request->getPort() ? $request->getPort() : $this->httpsPort);
        $this->setQueryString($request->server->get('QUERY_STRING', ''));

        return $this;
    }

    /**
     * Gets the base URL.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets the base URL.
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $baseUrl = backport_type_check('string', $baseUrl);

        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Gets the path info.
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * Sets the path info.
     *
     * @return $this
     */
    public function setPathInfo($pathInfo)
    {
        $pathInfo = backport_type_check('string', $pathInfo);

        $this->pathInfo = $pathInfo;

        return $this;
    }

    /**
     * Gets the HTTP method.
     *
     * The method is always an uppercased string.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the HTTP method.
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $method = backport_type_check('string', $method);

        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Gets the HTTP host.
     *
     * The host is always lowercased because it must be treated case-insensitive.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the HTTP host.
     *
     * @return $this
     */
    public function setHost($host)
    {
        $host = backport_type_check('string', $host);

        $this->host = strtolower($host);

        return $this;
    }

    /**
     * Gets the HTTP scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the HTTP scheme.
     *
     * @return $this
     */
    public function setScheme($scheme)
    {
        $scheme = backport_type_check('string', $scheme);

        $this->scheme = strtolower($scheme);

        return $this;
    }

    /**
     * Gets the HTTP port.
     *
     * @return int
     */
    public function getHttpPort()
    {
        return $this->httpPort;
    }

    /**
     * Sets the HTTP port.
     *
     * @return $this
     */
    public function setHttpPort($httpPort)
    {
        $httpPort = backport_type_check('int', $httpPort);

        $this->httpPort = $httpPort;

        return $this;
    }

    /**
     * Gets the HTTPS port.
     *
     * @return int
     */
    public function getHttpsPort()
    {
        return $this->httpsPort;
    }

    /**
     * Sets the HTTPS port.
     *
     * @return $this
     */
    public function setHttpsPort($httpsPort)
    {
        $httpsPort = backport_type_check('int', $httpsPort);

        $this->httpsPort = $httpsPort;

        return $this;
    }

    /**
     * Gets the query string without the "?".
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Sets the query string.
     *
     * @return $this
     */
    public function setQueryString($queryString = null)
    {
        $queryString = backport_type_check('?string', $queryString);

        // string cast to be fault-tolerant, accepting null
        $this->queryString = (string) $queryString;

        return $this;
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters.
     *
     * @param array $parameters The parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Gets a parameter value.
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        $name = backport_type_check('string', $name);

        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * Checks if a parameter value is set for the given parameter.
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        $name = backport_type_check('string', $name);

        return \array_key_exists($name, $this->parameters);
    }

    /**
     * Sets a parameter value.
     *
     * @param mixed $parameter The parameter value
     *
     * @return $this
     */
    public function setParameter($name, $parameter)
    {
        $name = backport_type_check('string', $name);

        $this->parameters[$name] = $parameter;

        return $this;
    }

    public function isSecure() //// bool
    {
        return 'https' === $this->scheme;
    }
}
