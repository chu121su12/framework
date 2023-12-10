<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use Orchestra\Testbench\PHPUnit\AttributeParser;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\Metadata\Annotation\Parser\Registry as PHPUnit10Registry;
use PHPUnit\Util\Annotation\Registry as PHPUnit9Registry;
use ReflectionClass;

use function Orchestra\Testbench\phpunit_version_compare;

trait InteractsWithPHPUnitMethodOnly
{
    /**
     * Determine if the trait is used within testing.
     *
     * @return bool
     */
    public function isRunningTestCase()/*: bool*/
    {
        return $this instanceof PHPUnitTestCase || static::usesTestingConcern();
    }

    /**
     * Resolve PHPUnit method annotations.
     *
     * @phpunit-overrides
     *
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    protected function resolvePhpUnitAnnotations()/*: Collection*/
    {
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            return new Collection();
        }

        $instance = new ReflectionClass($this);

        if (! $this instanceof PHPUnitTestCase || ! method_exists($instance, 'isAnonymous') && $instance->isAnonymous()) {
            return new Collection();
        }

        if (! (class_exists('PHPUnit\Metadata\Annotation\Parser\Registry') || class_exists('PHPUnit\Util\Annotation\Registry'))) {
            return new Collection();
        }

        list($registry, $methodName) = phpunit_version_compare('10', '>=')
            ? [\PHPUnit\Metadata\Annotation\Parser\Registry::getInstance(), $this->name()] /** @phpstan-ignore-line */
            : [\PHPUnit\Util\Annotation\Registry::getInstance(), $this->getName(false)]; /** @phpstan-ignore-line */

        /** @var array<string, mixed> $annotations */
        $annotations = rescue(
            function () use ($registry, $instance, $methodName) { return $registry->forMethod($instance->getName(), $methodName)->symbolAnnotations(); },
            [],
            false
        );

        return Collection::make($annotations);
    }

    /**
     * Resolve PHPUnit method attributes.
     *
     * @phpunit-overrides
     *
     * @return \Illuminate\Support\Collection<class-string, array<int, object>>
     */
    protected function resolvePhpUnitAttributes()/*: Collection*/
    {
        $instance = new ReflectionClass($this);

        if (! $this instanceof PHPUnitTestCase || ! method_exists($instance, 'isAnonymous') || $instance->isAnonymous()) {
            return new Collection();
        }

        $className = $instance->getName();
        $methodName = phpunit_version_compare('10', '>=')
            ? $this->name() /** @phpstan-ignore-line */
            : $this->getName(false); /** @phpstan-ignore-line */
        if (! isset(static::$cachedTestCaseClassAttributes[$className])) {
            static::$cachedTestCaseClassAttributes[$className] = rescue(static function () use ($className) {
                return AttributeParser::forClass($className);
            }, [], false);
        }

        if (! isset(static::$cachedTestCaseMethodAttributes["{$className}:{$methodName}"])) {
            static::$cachedTestCaseMethodAttributes["{$className}:{$methodName}"] = rescue(static function () use ($className, $methodName) {
                return AttributeParser::forMethod($className, $methodName);
            }, [], false);
        }

        $attributes = Collection::make(array_merge(
            static::$cachedTestCaseClassAttributes[$className],
            static::$cachedTestCaseMethodAttributes["{$className}:{$methodName}"]
        ))->groupBy('key')
            ->map(static function ($attributes) {
                /** @var \Illuminate\Support\Collection<int, array{key: class-string, instance: object}> $attributes */
                return $attributes->map(static function ($attribute) {
                    /** @var array{key: class-string, instance: object} $attribute */
                    return $attribute['instance'];
                });
            });

        /** @var \Illuminate\Support\Collection<class-string, array<int, object>> $attributes */
        return $attributes;
    }

    /**
     * Determine if the trait is used Orchestra\Testbench\Concerns\Testing trait.
     *
     * @param  class-string|null  $trait
     * @return bool
     */
    public static function usesTestingConcern(/*?string */$trait = null)/*: bool*/
    {
        $trait = backport_type_check('?string', $trait);

        return isset(static::cachedUsesForTestCase()[isset($trait) ? $trait : Testing::class]);
    }

    /**
     * Define or get the cached uses for test case.
     *
     * @return array<class-string, class-string>
     */
    public static function cachedUsesForTestCase()/*: array*/
    {
        if (\is_null(static::$cachedTestCaseUses)) {
            /** @var array<class-string, class-string> $uses */
            $uses = array_flip(class_uses_recursive(static::class));

            static::$cachedTestCaseUses = $uses;
        }

        return static::$cachedTestCaseUses;
    }

    /**
     * Prepare the testing environment before the running the test case.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function setupBeforeClassUsingPHPUnit()/*: void*/
    {
        static::cachedUsesForTestCase();
    }

    /**
     * Clean up the testing environment before the next test case.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public static function teardownAfterClassUsingPHPUnit()/*: void*/
    {
        static::$cachedTestCaseUses = null;
        static::$cachedTestCaseClassAttributes = [];
        static::$cachedTestCaseMethodAttributes = [];

        foreach ([
            \PHPUnit\Util\Annotation\Registry::class,
            \PHPUnit\Metadata\Annotation\Parser\Registry::class,
        ] as $class) {
            if (class_exists($class)) {
                backport_function_call_able(function () {
                    $this->classDocBlocks = [];
                    $this->methodDocBlocks = [];
                })->call($class::getInstance());
            }
        }
    }
}
