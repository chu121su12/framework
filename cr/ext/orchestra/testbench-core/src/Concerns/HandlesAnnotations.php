<?php

namespace Orchestra\Testbench\Concerns;

use Closure;
use Illuminate\Support\Collection;
use function Orchestra\Testbench\phpunit_version_compare;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\Parser\Registry as PHPUnit10Registry;
// use PHPUnit\Runner\Version;
use PHPUnit\Util\Annotation\Registry as PHPUnit9Registry;
use ReflectionClass;

/**
 * @internal
 */
trait HandlesAnnotations
{
    /**
     * Parse test method annotations.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  string  $name
     */
    protected function parseTestMethodAnnotations($app, /*string */$name, /*?*/Closure $callback = null)/*: void*/
    {
        $name = backport_type_check('string', $name);

        $this->resolvePhpUnitAnnotations()
            ->lazy()
            ->filter(static function ($actions, /*string */$key) use ($name) {
                $key = backport_type_check('string', $key);
                return $key === $name && ! empty($actions);
            })->flatten()
            ->filter(function ($method) { return \is_string($method) && method_exists($this, $method); })
            ->each(isset($callback) ? $callback : function ($method) use ($app) {
                $this->{$method}($app);
            });
    }

    /**
     * Resolve PHPUnit method annotations.
     *
     * @phpunit-overrides
     *
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    abstract protected function resolvePhpUnitAnnotations()/*: Collection*/;
}
