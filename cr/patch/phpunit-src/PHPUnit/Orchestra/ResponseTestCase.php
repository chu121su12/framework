<?php

namespace PHPUnit\Orchestra;

use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase;

class ResponseTestCase extends TestCase
{
    protected $baseResponse;
    protected $baseException;

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->baseResponse = null;
        $this->baseException = null;

        try {
            $this->baseResponse = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } catch (\Exception $e) {
            $this->baseException = $e;
        }

        $this->baseResponse = TestResponse::fromBaseResponse($this->baseResponse);

        return $this;
    }

    public function __call($method, $args)
    {
        if (!$this->baseException) {
            return $this->baseResponse->{$method}(...$args);
        }

        return;

        if (isset($this->baseException)) {
            if (in_array($method, [
                'getStatusCode',
                'getHeaders',
                'getMessage',
            ])) {
                return $this->baseException->{$method}(...$args);
            }

            if ($method === 'getContent') {
                // try to check content of baseException
                return $this->baseException->getMessage(...$args);
            }
        }
    }
}
