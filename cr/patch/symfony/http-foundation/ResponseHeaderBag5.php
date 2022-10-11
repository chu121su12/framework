<?php

namespace Symfony\Component\HttpFoundation;

use CR\LaravelBackport\SymfonyHelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseHeaderBag5 extends ResponseHeaderBag
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $headers = [])
    {
        parent::__construct($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function makeDisposition(/*string */$disposition, /*string */$filename, /*string */$filenameFallback = '')
    {
        $disposition = backport_type_check('string', $disposition);
        $filename = backport_type_check('string', $filename);
        $filenameFallback = backport_type_check('string', $filenameFallback);

        return SymfonyHelper::httpFoundationMakeDisposition($disposition, $filename, $filenameFallback);
    }
}
