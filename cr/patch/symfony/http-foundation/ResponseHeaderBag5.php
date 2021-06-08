<?php

namespace Symfony\Component\HttpFoundation;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseHeaderBag5 extends ResponseHeaderBag
{
    public function __construct(array $headers = [])
    {
        parent::__construct($headers);
    }

    public function makeDisposition($disposition, $filename, $filenameFallback = '')
    {
        return SymfonyHelper::httpFoundationMakeDisposition($disposition, $filename, $filenameFallback);
    }
}
