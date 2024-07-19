<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (PHP_VERSION_ID >= 80100) {
    include_once 'Enums.php';
}

/**
 * @requires PHP 8.1
 */
class ValidationEnumRuleTest extends TestCase
{
    public function testValidationPassesWhenPassingCorrectEnum()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => 'pending',
                'int_status' => 1,
            ],
            [
                'status' => new Enum(StringStatus::class),
                'int_status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationPassesWhenPassingInstanceOfEnum()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => StringStatus::done,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationPassesWhenPassingInstanceOfPureEnum()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => PureEnum::one,
            ],
            [
                'status' => new Enum(PureEnum::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsWhenProvidingNoExistingCases()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => 'finished',
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesForAllCasesUntilEitherOnlyOrExceptIsPassed()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status_1' => PureEnum::one,
                'status_2' => PureEnum::two,
                'status_3' => with(IntegerStatus::done)->value,
            ],
            [
                'status_1' => new Enum(PureEnum::class),
                'status_2' => (new Enum(PureEnum::class))->only([])->except([]),
                'status_3' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertTrue($v->passes());
    }

    /**
     * @dataProvider conditionalCasesDataProvider
     */
    #[DataProvider('conditionalCasesDataProvider')]
    public function testValidationPassesWhenOnlyCasesProvided(
        /*IntegerStatus|int */$enum,
        /*array|Arrayable|IntegerStatus */$only,
        /*bool */$expected
    ) {
        $expected = backport_type_check('bool', $expected);

        $only = backport_type_check(['array', Arrayable::class, IntegerStatus::class], $only);

        $enum = backport_type_check([IntegerStatus::class, 'int'], $enum);

        $v = new Validator(
            resolve('translator'),
            [
                'status' => $enum,
            ],
            [
                'status' => (new Enum(IntegerStatus::class))->only($only),
            ]
        );

        $this->assertSame($expected, $v->passes());
    }

    /**
     * @dataProvider conditionalCasesDataProvider
     */
    #[DataProvider('conditionalCasesDataProvider')]
    public function testValidationPassesWhenExceptCasesProvided(
        /*int|IntegerStatus */$enum,
        /*array|Arrayable|IntegerStatus */$except,
        /*bool */$expected
    ) {
        $expected = backport_type_check('bool', $expected);

        $only = backport_type_check(['array', Arrayable::class, IntegerStatus::class], $except);

        $enum = backport_type_check([IntegerStatus::class, 'int'], $enum);

        $v = new Validator(
            resolve('translator'),
            [
                'status' => $enum,
            ],
            [
                'status' => (new Enum(IntegerStatus::class))->except($except),
            ]
        );

        $this->assertSame($expected, $v->fails());
    }

    public function testOnlyHasHigherOrderThanExcept()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => PureEnum::one,
            ],
            [
                'status' => (new Enum(PureEnum::class))
                    ->only(PureEnum::one)
                    ->except(PureEnum::one),
            ]
        );

        $this->assertTrue($v->passes());
    }

    public function testValidationFailsWhenProvidingDifferentType()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => 10,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesWhenProvidingDifferentTypeThatIsCastableToTheEnumType()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => '1',
            ],
            [
                'status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsWhenProvidingNull()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => null,
            ],
            [
                'status' => new Enum(StringStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public function testValidationPassesWhenProvidingNullButTheFieldIsNullable()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => null,
            ],
            [
                'status' => ['nullable', new Enum(StringStatus::class)],
            ]
        );

        $this->assertFalse($v->fails());
    }

    public function testValidationFailsOnPureEnum()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => 'one',
            ],
            [
                'status' => ['required', new Enum(PureEnum::class)],
            ]
        );

        $this->assertTrue($v->fails());
    }

    public function testValidationFailsWhenProvidingStringToIntegerType()
    {
        $v = new Validator(
            resolve('translator'),
            [
                'status' => 'abc',
            ],
            [
                'status' => new Enum(IntegerStatus::class),
            ]
        );

        $this->assertTrue($v->fails());
        $this->assertEquals(['The selected status is invalid.'], $v->messages()->get('status'));
    }

    public static function conditionalCasesDataProvider()/*: array*/
    {
        if (! \class_exists(IntegerStatus::class)) {
            return [];
        }

        return [
            [IntegerStatus::done, IntegerStatus::done, true],
            [IntegerStatus::done, [IntegerStatus::done, IntegerStatus::pending], true],
            [IntegerStatus::done, new ArrayObject([IntegerStatus::done, IntegerStatus::pending]), true],
            [IntegerStatus::done, new Collection([IntegerStatus::done, IntegerStatus::pending]), true],
            // [IntegerStatus::pending->value, [IntegerStatus::done, IntegerStatus::pending], true],
            // [IntegerStatus::done->value, IntegerStatus::pending, false],
        ];
    }

    protected function setUp()/*: void*/
    {
        $container = Container::getInstance();

        $container->bind('translator', function () {
            return new Translator(
                new ArrayLoader, 'en'
            );
        });

        Facade::setFacadeApplication($container);

        (new ValidationServiceProvider($container))->register();
    }

    protected function tearDown()/*: void*/
    {
        Container::setInstance(null);

        Facade::clearResolvedInstances();

        Facade::setFacadeApplication(null);
    }
}
