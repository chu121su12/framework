<?php

namespace Symfony\Component\HttpFoundation;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\HttpFoundation\Cookie;

class Cookie6 extends Cookie
{
    private $partitioned = false;

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
}
