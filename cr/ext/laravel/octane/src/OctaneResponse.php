<?php

namespace Laravel\Octane;

use Symfony\Component\HttpFoundation\Response;

class OctaneResponse
{
    public $response;

    public $outputBuffer;

    public function __construct(/*public */Response $response, /*public ?string */$outputBuffer = null)
    {
        $this->response = $response;

        $this->outputBuffer = backport_type_check('?string', $outputBuffer);
    }
}
