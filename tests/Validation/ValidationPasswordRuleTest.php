<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationPasswordRuleTest_testPassesWithCustomRules_class implements RuleContract
        {
            public function passes($attribute, $value)
            {
                return $value === 'aa';
            }

            public function message()
            {
                return 'Custom rule object failed';
            }
        }

class ValidationPasswordRuleTest extends TestCase
{
    use \PHPUnit\Framework\PhpUnit8Assert;

    public function testString()
    {
        $this->fails(Password::min(3), [['foo' => 'bar'], ['foo']], [
            'validation.string',
            'validation.min.string',
        ]);

        $this->fails(Password::min(3), [1234567, 545], [
            'validation.string',
        ]);

        $this->passes(Password::min(3), ['abcd', '454qb^', '接2133手田']);
    }

    public function testMin()
    {
        $this->fails(new Password(8), ['a', 'ff', '12'], [
            'validation.min.string',
        ]);

        $this->fails(Password::min(3), ['a', 'ff', '12'], [
            'validation.min.string',
        ]);

        $this->passes(Password::min(3), ['333', 'abcd']);
        $this->passes(new Password(8), ['88888888']);
    }

    public function testConditional()
    {
        $is_privileged_user = true;
        $rule = (new Password(8))->when($is_privileged_user, function ($rule) {
            $rule->symbols();
        });

        $this->fails($rule, ['aaaaaaaa', '11111111'], [
            'validation.password.symbols',
        ]);

        $is_privileged_user = false;
        $rule = (new Password(8))->when($is_privileged_user, function ($rule) {
            $rule->symbols();
        });

        $this->passes($rule, ['aaaaaaaa', '11111111']);
    }

    public function testMixedCase()
    {
        $this->fails(Password::min(2)->mixedCase(), ['nn', 'MM'], [
            'validation.password.mixed',
        ]);

        $this->passes(Password::min(2)->mixedCase(), ['Nn', 'Mn', 'âA']);
    }

    public function testLetters()
    {
        $this->fails(Password::min(2)->letters(), ['11', '22', '^^', '``', '**'], [
            'validation.password.letters',
        ]);

        $this->passes(Password::min(2)->letters(), ['1a', 'b2', 'â1', '1 京都府']);
    }

    public function testNumbers()
    {
        $this->fails(Password::min(2)->numbers(), ['aa', 'bb', '  a', '京都府'], [
            'validation.password.numbers',
        ]);

        $this->passes(Password::min(2)->numbers(), ['1a', 'b2', '00', '京都府 1']);
    }

    public function testDefaultRules()
    {
        $this->fails(Password::min(3), [null], [
            'validation.string',
            'validation.min.string',
        ]);
    }

    public function testSymbols()
    {
        $this->fails(Password::min(2)->symbols(), ['ab', '1v'], [
            'validation.password.symbols',
        ]);

        $this->passes(Password::min(2)->symbols(), ['n^d', 'd^!', 'âè$', '金廿土弓竹中；']);
    }

    /**
     * @requires OS Linux|Darwin
     */
    public function testUncompromised()
    {
        $this->fails(Password::min(2)->uncompromised(), [
            '123456',
            'password',
            'welcome',
            'abc123',
            '123456789',
            '12345678',
            'nuno',
        ], [
            'validation.password.uncompromised',
        ]);

        $this->passes(Password::min(2)->uncompromised(9999999), [
            'nuno',
        ]);

        $this->passes(Password::min(2)->uncompromised(), [
            '手田日尸Ｚ難金木水口火女月土廿卜竹弓一十山',
            '!p8VrB',
            '&xe6VeKWF#n4',
            '%HurHUnw7zM!',
            'rundeliekend',
            '7Z^k5EvqQ9g%c!Jt9$ufnNpQy#Kf',
            'NRs*Gz2@hSmB$vVBSPDfqbRtEzk4nF7ZAbM29VMW$BPD%b2U%3VmJAcrY5eZGVxP%z%apnwSX',
        ]);
    }

    public function testMessagesOrder()
    {
        $makeRules = function () {
            return ['required', Password::min(8)->mixedCase()->numbers()];
        };

        $this->fails($makeRules(), [null], [
            'validation.required',
        ]);

        $this->fails($makeRules(), ['foo', 'azdazd'], [
            'validation.min.string',
            'validation.password.mixed',
            'validation.password.numbers',
        ]);

        $this->fails($makeRules(), ['1231231'], [
            'validation.min.string',
            'validation.password.mixed',
        ]);

        $this->fails($makeRules(), ['4564654564564'], [
            'validation.password.mixed',
        ]);

        $this->fails($makeRules(), ['aaaaaaaaa', 'TJQSJQSIUQHS'], [
            'validation.password.mixed',
            'validation.password.numbers',
        ]);

        $this->passes($makeRules(), ['4564654564564Abc']);

        $makeRules = function () {
            return ['nullable', 'confirmed', Password::min(8)->letters()->symbols()->uncompromised()];
        };

        $this->passes($makeRules(), [null]);

        $this->fails($makeRules(), ['foo', 'azdazd'], [
            'validation.min.string',
            'validation.password.symbols',
        ]);

        $this->fails($makeRules(), ['1231231'], [
            'validation.min.string',
            'validation.password.letters',
            'validation.password.symbols',
        ]);

        $this->fails($makeRules(), ['aaaaaaaaa', 'TJQSJQSIUQHS'], [
            'validation.password.symbols',
        ]);

        $this->fails($makeRules(), ['4564654564564'], [
            'validation.password.letters',
            'validation.password.symbols',
        ]);

        $this->fails($makeRules(), ['abcabcabc!'], [
            'validation.password.uncompromised',
        ]);

        $v = new Validator(
            resolve('translator'),
            ['my_password' => 'Nuno'],
            ['my_password' => ['nullable', 'confirmed', Password::min(3)->letters()]]
        );

        $this->assertFalse($v->passes());

        $this->assertSame(
            ['my_password' => ['validation.confirmed']],
            $v->messages()->toArray()
        );
    }

    public function testItCanUseDefault()
    {
        $this->assertInstanceOf(Password::class, Password::default_());
    }

    public function testItCanSetDefaultUsing()
    {
        $this->assertInstanceOf(Password::class, Password::default_());

        $password = Password::min(3);
        $password2 = Password::min(2)->mixedCase();

        Password::defaults(function () use ($password) {
            return $password;
        });

        $this->passes(Password::default_(), ['abcd', '454qb^', '接2133手田']);
        $this->assertSame($password, Password::default_());
        $this->assertSame(['required', $password], Password::required());
        $this->assertSame(['sometimes', $password], Password::sometimes());

        Password::defaults($password2);
        $this->passes(Password::default_(), ['Nn', 'Mn', 'âA']);
        $this->assertSame($password2, Password::default_());
        $this->assertSame(['required', $password2], Password::required());
        $this->assertSame(['sometimes', $password2], Password::sometimes());
    }

    public function testItCannotSetDefaultUsingGivenString()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('given callback should be callable');

        Password::defaults('required|password');
    }

    public function testItPassesWithValidDataIfTheSameValidationRulesAreReused()
    {
        $rules = [
            'password' => Password::default_(),
        ];

        $v = new Validator(
            resolve('translator'),
            ['password' => '1234'],
            $rules
        );

        $this->assertFalse($v->passes());

        $v1 = new Validator(
            resolve('translator'),
            ['password' => '12341234'],
            $rules
        );

        $this->assertTrue($v1->passes());
    }

    public function testCustomMessages()
    {
        $rules = [
            'my_password' => Password::min(6)->letters(),
        ];

        $messages = [
            'min' => 'Message for validating length',
            'password.letters' => 'Message for validating letters',
        ];

        $v = new Validator(
            resolve('translator'),
            ['my_password' => '1234'],
            $rules,
            $messages
        );

        $this->assertFalse($v->passes());

        $this->assertSame(
            ['my_password' => array_values($messages)],
            $v->messages()->toArray()
        );
    }

    public function testPassesWithCustomRules()
    {
        $closureRule = function ($attribute, $value, $fail) {
            if ($value !== 'aa') {
                $fail('Custom rule closure failed');
            }
        };

        $ruleObject = new ValidationPasswordRuleTest_testPassesWithCustomRules_class;

        $this->passes(Password::min(2)->rules($closureRule), ['aa']);
        $this->passes(Password::min(2)->rules([$closureRule]), ['aa']);
        $this->passes(Password::min(2)->rules($ruleObject), ['aa']);
        $this->passes(Password::min(2)->rules([$closureRule, $ruleObject]), ['aa']);

        $this->fails(Password::min(2)->rules($closureRule), ['ab'], [
            'Custom rule closure failed',
        ]);

        $this->fails(Password::min(2)->rules($ruleObject), ['ab'], [
            'Custom rule object failed',
        ]);
    }

    protected function passes($rule, $values)
    {
        $this->assertValidationRules($rule, $values, true, []);
    }

    protected function fails($rule, $values, $messages)
    {
        $this->assertValidationRules($rule, $values, false, $messages);
    }

    protected function assertValidationRules($rule, $values, $result, $messages)
    {
        $this->skipWithInactiveSocketConnection($this, 'api.pwnedpasswords.com');
        
        foreach ($values as $value) {
            $v = new Validator(
                resolve('translator'),
                ['my_password' => $value, 'my_password_confirmation' => $value],
                ['my_password' => is_object($rule) ? clone $rule : $rule]
            );

            $this->assertSame($result, $v->passes());

            $this->assertSame(
                $result ? [] : ['my_password' => $messages],
                $v->messages()->toArray()
            );
        }
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

        Password::$defaultCallback = null;
    }
}
