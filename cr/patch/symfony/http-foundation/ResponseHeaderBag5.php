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

    public function makeDisposition(/*string */$disposition, /*string */$filename, /*string */$filenameFallback = '')
    {
        $disposition = cast_to_string($disposition);
        $filename = cast_to_string($filename);
        $filenameFallback = cast_to_string($filenameFallback);

        return SymfonyHelper::httpFoundationMakeDisposition($disposition, $filename, $filenameFallback);
    }
}
