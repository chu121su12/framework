<?php

namespace Facade\Ignition\Context;

use Facade\FlareClient\Context\ContextDetectorInterface;
use Facade\FlareClient\Context\ContextInterface;
use Illuminate\Http\Request;

class LaravelContextDetector implements ContextDetectorInterface
{
    public function detectCurrentContext()/*: ContextInterface*/
    {
        if (app()->runningInConsole()) {
            return new LaravelConsoleContext(isset($_SERVER['argv']) ? $_SERVER['argv'] : []);
        }

        return new LaravelRequestContext(app(Request::class));
    }
}
