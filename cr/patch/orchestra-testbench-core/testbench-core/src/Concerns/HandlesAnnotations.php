<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use ReflectionClass;

trait HandlesAnnotations
{
    /**
     * Parse test method annotations.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  string  $name
     */
    protected function parseTestMethodAnnotations($app, /*string */$name)////: void
    {
        $name = cast_to_string($name);

        $instance = new ReflectionClass($this);

        if (! $this instanceof TestCase || (method_exists($instance, 'isAnonymous') && $instance->isAnonymous())) {
            return;
        }

        if (!class_exists('PHPUnit\Util\Annotation\Registry')) {
            $annotations = [];

            Collection::make($annotations)->each(function ($actions) use ($name, $app) {
                Collection::make(isset($actions[$name]) ? $actions[$name] : [])
                    ->filter(function ($method) {
                        return ! \is_null($method) && method_exists($this, $method);
                    })->each(function ($method) use ($app) {
                        $this->{$method}($app);
                    });
            });

            return;
        }

        if (class_exists(Version::class) && version_compare(Version::id(), '10', '>=')) {
            $registry = \PHPUnit\Metadata\Annotation\Parser\Registry::getInstance();
        } else {
            $registry = \PHPUnit\Util\Annotation\Registry::getInstance();
        }

        Collection::make(
            rescue(function () use ($registry) {
                return $registry->forMethod(static::class, $this->getName(false))->symbolAnnotations();
            }, [], false)
        )->filter(static function ($actions, $key) use ($name) {
            return $key === $name;
        })->each(function ($actions) use ($app) {
            Collection::make(isset($actions) ? $actions : [])
                ->filter(function ($method) {
                    return ! \is_null($method) && method_exists($this, $method);
                })->each(function ($method) use ($app) {
                    $this->{$method}($app);
                });
        });
    }
}
