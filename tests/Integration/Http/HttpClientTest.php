<?php

namespace Illuminate\Tests\Integration\Http;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class HttpClientTest extends TestCase
{
    public function testGlobalMiddlewarePersistsAfterFacadeFlush()/*: void*/
    {
        Http::macro('getGlobalMiddleware', function () { return $this->globalMiddleware; });
        Http::globalRequestMiddleware(function ($request) { return $request->withHeader('User-Agent', 'Example Application/1.0'); });
        Http::globalRequestMiddleware(function ($request) { return $request->withHeader('User-Agent', 'Example Application/1.0'); });

        $this->assertCount(2, Http::getGlobalMiddleware());

        Facade::clearResolvedInstances();

        $this->assertCount(2, Http::getGlobalMiddleware());
    }
}
