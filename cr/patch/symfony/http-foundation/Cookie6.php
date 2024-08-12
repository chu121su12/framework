<?php

namespace Symfony\Component\HttpFoundation;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\HttpFoundation\Cookie;

class Cookie6 extends Cookie
{
    private $partitioned = false;
    private $secureDefault = false;

    public function __construct(
        $name,
        $value = null,
        $expire = 0,
        $path = '/',
        $domain = null,
        $secure = null,
        $httpOnly = true,
        $raw = false,
        $sameSite = self::SAMESITE_LAX,
        $partitioned = false)
    {
        parent::__construct(
            $name,
            $value,
            $expire,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $raw,
            $sameSite
        );

        $this->secure = $secure;
        $this->partitioned = $partitioned;
    }

    #[\ReturnTypeWillChange]
    public function __toString()
    {
        if ($this->partitioned) {
            return parent::__toString() . '; partitioned';
        }

        return parent::__toString();
    }

    public function isPartitioned()
    {
        return $this->partitioned;
    }

    public function isSecure()
    {
        return (bool) (isset($this->secure) ? $this->secure : $this->secureDefault);
    }

    public function setSecureDefault($default)
    {
        $this->secureDefault = (bool) $default;
    }

    public static function cloneWithNewValue(Cookie $cookie, $value)
    {
        return new Cookie6(
            $cookie->name,
            $value,
            $cookie->expire,
            $cookie->path,
            $cookie->domain,
            $cookie->secure,
            $cookie->httpOnly,
            $cookie->isRaw(),
            $cookie->getSameSite(),
            \method_exists($cookie, 'isPartitioned') ? $cookie->isPartitioned() : false
        );
    }
}
