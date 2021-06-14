<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BinaryFileResponse5 extends BinaryFileResponse
{

    public function __construct($file, /*int */$status = 200, array $headers = [], /*bool */$public = true, /*string */$contentDisposition = null, /*bool */$autoEtag = false, /*bool */$autoLastModified = true)
    {
        $status = cast_to_int($status);

        $public = cast_to_bool($public);

        $contentDisposition = cast_to_string($contentDisposition, null);

        $autoEtag = cast_to_bool($autoEtag);

        $autoLastModified = cast_to_bool($autoLastModified);

        parent::__construct($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);

        $this->headers = new ResponseHeaderBag5($headers);

        $this->setFile($file, $contentDisposition, $autoEtag, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }
}
