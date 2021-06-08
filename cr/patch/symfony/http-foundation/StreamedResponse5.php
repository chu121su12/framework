<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedResponse5 extends StreamedResponse
{
    public function __construct(callable $callback = null, /*int */$status = 200, array $headers = [])
    {
        $status = cast_to_int($status);

        parent::__construct($callback, $status, $headers);

        $this->headers = new ResponseHeaderBag5($headers);
    }
}
