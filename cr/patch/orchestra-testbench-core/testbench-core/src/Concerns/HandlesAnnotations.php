<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Test as TestUtil;

trait HandlesAnnotations
{
    /**
     * Parse test method annotations.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  string  $name
     */
    protected function parseTestMethodAnnotations($app, $name)
    {
        $name = cast_to_string($name);

        if (! $this instanceof TestCase) {
            return;
        }

        $annotations = [];

        Collection::make($annotations)->each(function ($location) use ($name, $app) {
            Collection::make(isset($location[$name]) ? $location[$name] : [])
                ->filter(function ($method) {
                    return ! \is_null($method) && \method_exists($this, $method);
                })->each(function ($method) use ($app) {
                    $this->{$method}($app);
                });
        });
    }
}
