<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use function Orchestra\Testbench\phpunit_version_compare;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Annotation\Parser\Registry as PHPUnit10Registry;
use PHPUnit\Util\Annotation\Registry as PHPUnit9Registry;
use ReflectionClass;

/**
 * @internal
 */
trait HandlesAnnotations
{
    /**
     * Resolve PHPUnit method annotations.
     *
     * @phpunit-overrides
     *
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    protected function resolvePhpUnitAnnotations()/*: Collection*/
    {
        $instance = new ReflectionClass($this);

        if (! $this instanceof TestCase || $instance->isAnonymous()) {
            return new Collection();
        }

        list($registry, $methodName) = phpunit_version_compare('10', '>=')
            ? [PHPUnit10Registry::getInstance(), $this->name()] /** @phpstan-ignore-line */
            : [PHPUnit9Registry::getInstance(), $this->getName(false)]; /** @phpstan-ignore-line */

        /** @var array<string, mixed> $annotations */
        $annotations = rescue(
            function () use ($registry, $instance, $methodName) { return $registry->forMethod($instance->getName(), $methodName)->symbolAnnotations(); },
            [],
            false
        );

        return Collection::make($annotations);
    }

    /**
     * Parse test method annotations.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @param  string  $name
     */
    protected function parseTestMethodAnnotations($app, /*string */$name)/*: void*/
    {
        $name = backport_type_check('string', $name);

        $this->resolvePhpUnitAnnotations()
            ->filter(function ($actions, /*string */$key) use ($name) {
                $key = backport_type_check('string', $key);

                return $key === $name && ! empty($actions);
            })
            ->each(function (array $actions) use ($app) {
                Collection::make($actions)
                    ->filter(function ($method) {
                        return \is_string($method) && method_exists($this, $method);
                    })
                    ->each(function (string $method) use ($app) {
                        $this->{$method}($app);
                    });
            });
    }

    /**
     * Clear parsed test method annotations.
     *
     * @phpunit-overrides
     *
     * @afterClass
     *
     * @return void
     */
    public static function clearParsedTestMethodAnnotations()/*: void*/
    {
        $registry = phpunit_version_compare('10', '>=')
            ? PHPUnit10Registry::getInstance() /** @phpstan-ignore-line */
            : PHPUnit9Registry::getInstance(); /** @phpstan-ignore-line */

        // Clear properties values from Registry class.
        $function = function () {
            $this->classDocBlocks = [];
            $this->methodDocBlocks = [];
        };
        $function->call($registry);
    }
}
