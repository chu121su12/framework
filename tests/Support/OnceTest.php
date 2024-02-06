<?php

namespace Illuminate\Tests\Support;

use Illuminate\Support\Once;
use PHPUnit\Framework\TestCase;

class OnceTest_testResultMemoization_class
        {
            public function rand()
            {
                return once(function () { return rand(1, PHP_INT_MAX); });
            }
        }

class OnceTest_testCallableIsCalledOnce_class
        {
            public /*int */$count = 0;

            public function increment()
            {
                return once(function () { return ++$this->count; });
            }
        }

class OnceTest_testIsNotMemoizedWhenCallableUsesChanges_class
        {
            public function rand(/*string */$letter)
            {
                $letter = backport_type_check('string', $letter);

                return once(function () use ($letter) {
                    return $letter.rand(1, 10000000);
                });
            }
        }

class OnceTest_testInvokables_class
        {
            public static $count = 0;

            public function __invoke()
            {
                return static::$count = static::$count + 1;
            }
        }

class OnceTest_testFirstClassCallableSyntax_class
        {
            public function rand()
            {
                return once(function (...$args) { return MyClass::staticRand(...$args); });
            }
        }

class OnceTest_testFirstClassCallableSyntaxWithArraySyntax_class
        {
            public function rand()
            {
                return once([MyClass::class, 'staticRand']);
            }
        }

class OnceTest_testResultIsMemoizedWhenCalledFromMethodsWithSameName_class
        {
            public function rand()
            {
                return once(function () { return rand(1, PHP_INT_MAX); });
            }
        }

class OnceTest_testRecursiveOnceCalls_class
        {
            public function rand()
            {
                return once(function () { return once(function () { return rand(1, PHP_INT_MAX); }); });
            }
        }

class OnceTest_testInvokables_class_2
        {
            protected $invokable;

            public function __construct(/*protected */$invokable)
            {
                $this->invokable = $invokable;
            }

            public function call()
            {
                return once($this->invokable);
            }
        }

class OnceTest_testResultIsMemoizedWhenCalledFromMethodsWithSameName_class_2
        {
            public function rand()
            {
                return once(function () { return rand(1, PHP_INT_MAX); });
            }
        }

class OnceTest extends TestCase
{
    protected function tearDown()/*: void*/
    {
        parent::tearDown();

        Once::flush();
        Once::enable();
    }

    public function testResultMemoization()
    {
        $instance = new OnceTest_testResultMemoization_class;

        $first = $instance->rand();
        $second = $instance->rand();

        $this->assertSame($first, $second);
    }

    public function testCallableIsCalledOnce()
    {
        $instance = new OnceTest_testCallableIsCalledOnce_class;

        $first = $instance->increment();
        $second = $instance->increment();

        $this->assertSame(1, $first);
        $this->assertSame(1, $second);
        $this->assertSame(1, $instance->count);
    }

    public function testFlush()
    {
        $instance = new MyClass();

        $first = $instance->rand();

        Once::flush();

        $second = $instance->rand();

        $this->assertNotSame($first, $second);

        Once::disable();
        Once::flush();

        $first = $instance->rand();
        $second = $instance->rand();

        $this->assertNotSame($first, $second);
    }

    public function testNotMemoizedWhenObjectIsGarbageCollected()
    {
        $instance = new MyClass();

        $first = $instance->rand();
        unset($instance);
        gc_collect_cycles();
        Once::flush();
        $instance = new MyClass();
        $second = $instance->rand();

        $this->assertNotSame($first, $second);
    }

    public function testIsNotMemoizedWhenCallableUsesChanges()
    {
        $instance = new OnceTest_testIsNotMemoizedWhenCallableUsesChanges_class;

        $first = $instance->rand('a');
        $second = $instance->rand('b');

        $this->assertNotSame($first, $second);

        $first = $instance->rand('a');
        $second = $instance->rand('a');

        $this->assertSame($first, $second);

        $results = [];
        $letter = 'a';

        a:
        $results[] = once(function () use ($letter) { return $letter.rand(1, 10000000); });

        if (count($results) < 2) {
            goto a;
        }

        $this->assertSame($results[0], $results[1]);
    }

    public function testUsageOfThis()
    {
        $instance = new MyClass();

        $first = $instance->callRand();
        $second = $instance->callRand();

        $this->assertSame($first, $second);
    }

    public function testInvokables()
    {
        $invokable = new OnceTest_testInvokables_class;

        $instance = new OnceTest_testInvokables_class_2($invokable);

        $first = $instance->call();
        $second = $instance->call();
        $third = $instance->call();

        $this->assertSame($first, $second);
        $this->assertSame($first, $third);
        $this->assertSame(1, $invokable::$count);
    }

    public function testFirstClassCallableSyntax()
    {
        $instance = new OnceTest_testFirstClassCallableSyntax_class;

        $first = $instance->rand();
        $second = $instance->rand();

        $this->assertSame($first, $second);
    }

    public function testFirstClassCallableSyntaxWithArraySyntax()
    {
        $instance = new OnceTest_testFirstClassCallableSyntaxWithArraySyntax_class;

        $first = $instance->rand();
        $second = $instance->rand();

        $this->assertSame($first, $second);
    }

    public function testStaticMemoization()
    {
        $first = MyClass::staticRand();
        $second = MyClass::staticRand();

        $this->assertSame($first, $second);
    }

    public function testMemoizationWhenOnceIsWithinClosure()
    {
        $resolver = function () { return once(function () { return rand(1, PHP_INT_MAX); }); };

        $first = $resolver();
        $second = $resolver();

        $this->assertSame($first, $second);
    }

    public function testMemoizationOnGlobalFunctions()
    {
        $first = my_rand();
        $second = my_rand();

        $this->assertSame($first, $second);
    }

    public function testDisable()
    {
        Once::disable();

        $first = my_rand();
        $second = my_rand();

        $this->assertNotSame($first, $second);
    }

    public function testTemporaryDisable()
    {
        $first = my_rand();
        $second = my_rand();

        Once::disable();

        $third = my_rand();

        Once::enable();

        $fourth = my_rand();

        $this->assertSame($first, $second);
        $this->assertNotSame($first, $third);
        $this->assertSame($first, $fourth);
    }

    public function testMemoizationWithinEvals()
    {
        $firstResolver = eval('return function () { return once( function () { return random_int(1, 1000); } ); } ;');

        $firstA = $firstResolver();
        $firstB = $firstResolver();

        $secondResolver = eval('return function () { return function () { return once( function () { return random_int(1, 1000); } ); }; } ;');

        $secondA = call_user_func($secondResolver());
        $secondB = call_user_func($secondResolver());

        $third = eval('return once( function () { return random_int(1, 1000); } ) ;');
        $fourth = eval('return once( function () { return random_int(1, 1000); } ) ;');

        $this->assertNotSame($firstA, $firstB);
        $this->assertNotSame($secondA, $secondB);
        $this->assertNotSame($third, $fourth);
    }

    public function testMemoizationOnSameLine()
    {
        $this->markTestSkipped('This test shows a limitation of the current implementation.');

        $result = [once(function () { return rand(1, PHP_INT_MAX); }), once(function () { return rand(1, PHP_INT_MAX); })];

        $this->assertNotSame($result[0], $result[1]);
    }

    public function testResultIsDifferentWhenCalledFromDifferentClosures()
    {
        $resolver = function () { return once(function () { return rand(1, PHP_INT_MAX); }); };
        $resolver2 = function () { return once(function () { return rand(1, PHP_INT_MAX); }); };

        $first = $resolver();
        $second = $resolver2();

        $this->assertNotSame($first, $second);
    }

    public function testResultIsMemoizedWhenCalledFromMethodsWithSameName()
    {
        $instanceA = new OnceTest_testResultIsMemoizedWhenCalledFromMethodsWithSameName_class;

        $instanceB = new OnceTest_testResultIsMemoizedWhenCalledFromMethodsWithSameName_class_2;

        $first = $instanceA->rand();
        $second = $instanceB->rand();

        $this->assertNotSame($first, $second);
    }

    public function testRecursiveOnceCalls()
    {
        $instance = new OnceTest_testRecursiveOnceCalls_class;

        $first = $instance->rand();
        $second = $instance->rand();

        $this->assertSame($first, $second);
    }

    public function testGlobalClosures()
    {
        $first = $GLOBALS['onceable1']();
        $second = $GLOBALS['onceable1']();

        $this->assertSame($first, $second);

        $third = $GLOBALS['onceable2']();
        $fourth = $GLOBALS['onceable2']();

        $this->assertSame($third, $fourth);

        $this->assertNotSame($first, $third);
    }
}

$letter = 'a';

$GLOBALS['onceable1'] = function () use ($letter) { return once(function () use ($letter) { return $letter.rand(1, PHP_INT_MAX); }); };
$GLOBALS['onceable2'] = function () use ($letter) { return once(function () use ($letter) { return $letter.rand(1, PHP_INT_MAX); }); };

function my_rand()
{
    return once(function () { return rand(1, PHP_INT_MAX); });
}

class MyClass
{
    public function rand()
    {
        return once(function () { return rand(1, PHP_INT_MAX); });
    }

    public static function staticRand()
    {
        return once(function () { return rand(1, PHP_INT_MAX); });
    }

    public function callRand()
    {
        return once(function () { return $this->rand(); });
    }
}
