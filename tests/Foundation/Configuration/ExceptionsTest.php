<?php

namespace Illuminate\Tests\Foundation\Configuration;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionsTest_testStopIgnoring_class extends Handler
        {
            public function getDontReport()/*: array*/
            {
                return array_merge($this->dontReport, $this->internalDontReport);
            }
        }

class ExceptionsTest extends TestCase
{
    public function tearDown()/*: void*/
    {
        parent::tearDown();
    }

    public function testStopIgnoring()
    {
        $container = new Container;
        $exceptions = new Exceptions($handler = new ExceptionsTest_testStopIgnoring_class($container));

        $this->assertContains(HttpException::class, $handler->getDontReport());
        $exceptions = $exceptions->stopIgnoring(HttpException::class);
        $this->assertInstanceOf(Exceptions::class, $exceptions);
        $this->assertNotContains(HttpException::class, $handler->getDontReport());

        $this->assertContains(ModelNotFoundException::class, $handler->getDontReport());
        $exceptions->stopIgnoring([ModelNotFoundException::class]);
        $this->assertNotContains(ModelNotFoundException::class, $handler->getDontReport());
    }

    public function testShouldRenderJsonWhen()
    {
        $exceptions = new Exceptions(new Handler(new Container));

        $shouldReturnJson = backport_function_call_able(function () { return $this->shouldReturnJson(new Request, new Exception()); })->call($exceptions->handler);
        $this->assertFalse($shouldReturnJson);

        $exceptions->shouldRenderJsonWhen(function () { return true; });
        $shouldReturnJson = backport_function_call_able(function () { return $this->shouldReturnJson(new Request, new Exception()); })->call($exceptions->handler);
        $this->assertTrue($shouldReturnJson);

        $exceptions->shouldRenderJsonWhen(function () { return false; });
        $shouldReturnJson = backport_function_call_able(function () { return $this->shouldReturnJson(new Request, new Exception()); })->call($exceptions->handler);
        $this->assertFalse($shouldReturnJson);
    }
}
