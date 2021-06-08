<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BinaryFileResponse5 extends BinaryFileResponse
{
    public function __construct($file, $status = 200, $headers = [], $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true)
    {
        parent::__construct($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);

        $this->headers = new ResponseHeaderBag5($headers);

        $this->setFile($file, $contentDisposition, $autoEtag, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }
}
