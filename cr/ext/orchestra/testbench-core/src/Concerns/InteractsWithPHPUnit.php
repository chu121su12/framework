<?php

namespace Orchestra\Testbench\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\Metadata\Annotation\Parser\Registry as PHPUnit10Registry;
use PHPUnit\Util\Annotation\Registry as PHPUnit9Registry;
use ReflectionClass;

use function Orchestra\Testbench\phpunit_version_compare;

trait InteractsWithPHPUnit
{
    /**
     * The cached uses for test case.
     *
     * @var array<class-string, class-string>|null
     */
    protected static $cachedTestCaseUses;

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

        if (! $this instanceof PHPUnitTestCase || (method_exists($instance, 'isAnonymous') && $instance->isAnonymous())) {
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
