<?php

namespace Illuminate\Tests\Integration\Routing;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware as Md;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class HasMiddlewareTest extends TestCase
{
    public function test_has_middleware_is_respected()
    {
        $route = Route::get('/', [HasMiddlewareTestController::class, 'index']);
        $this->assertEquals($route->controllerMiddleware(), ['all', 'only-index']);

        $route = Route::get('/', [HasMiddlewareTestController::class, 'show']);
        $this->assertEquals($route->controllerMiddleware(), ['all', 'except-index']);
    }
}

class HasMiddlewareTestController implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Md('all'),
            (new Md('only-index'))->only('index'),
            (new Md('except-index'))->except('index'),
        ];
    }

    public function index()
    {
        //
    }

    public function show()
    {
    }
}
