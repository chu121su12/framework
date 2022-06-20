<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ValidationInvokableRuleTest_testItCanPass_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                //
            }
        }

class ValidationInvokableRuleTest_testItCanFail_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail("The {$attribute} attribute is not 'foo'. Got '{$value}' instead.");
            }
        }

class ValidationInvokableRuleTest_testItCanReturnMultipleErrorMessages_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail('Error message 1.');
                $fail('Error message 2.');
            }
        }

class ValidationInvokableRuleTest_testItCanTranslateMessages_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail('validation.translated-error')->translate();
            }
        }

class ValidationInvokableRuleTest_testItCanAccessDataDuringValidation_class implements InvokableRule, DataAwareRule
        {
            public $data = [];

            public function setData($data)
            {
                $this->data = $data;
            }

            public function __invoke($attribute, $value, $fail)
            {
                if ($this->data === []) {
                    $fail('xxxx');
                }
            }
        }

class ValidationInvokableRuleTest_testItCanAccessValidatorDuringValidation_class implements InvokableRule, ValidatorAwareRule
        {
            public $validator = null;

            public function setValidator($validator)
            {
                $this->validator = $validator;
            }

            public function __invoke($attribute, $value, $fail)
            {
                if ($this->validator === null) {
                    $fail('xxxx');
                }
            }
        }

class ValidationInvokableRuleTest_testItCanBeExplicit_class implements InvokableRule
        {
            public $implicit = false;

            public function __invoke($attribute, $value, $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItCanBeImplicit_class implements InvokableRule
        {
            public $implicit = true;

            public function __invoke($attribute, $value, $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItIsExplicitByDefault_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItThrowsIfTranslationIsNotFound_class implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail('validation.key')->translate();
            }
        }

class ValidationInvokableRuleTest extends TestCase
{
    public function testItCanPass()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanPass_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->passes());
        $this->assertSame([], $validator->messages()->messages());
    }

    public function testItCanFail()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanFail_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                "The foo attribute is not 'foo'. Got 'bar' instead.",
            ],
        ], $validator->messages()->messages());
    }

    public function testItCanReturnMultipleErrorMessages()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanReturnMultipleErrorMessages_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'Error message 1.',
                'Error message 2.',
            ],
        ], $validator->messages()->messages());
    }

    public function testItCanTranslateMessages()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $trans->addLines(['validation.translated-error' => 'Translated error message.'], 'en');
        $rule = new ValidationInvokableRuleTest_testItCanTranslateMessages_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'Translated error message.',
            ],
        ], $validator->messages()->messages());
    }

    public function testItCanAccessDataDuringValidation()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanAccessDataDuringValidation_class();

        $validator = new Validator($trans, ['foo' => 'bar', 'bar' => 'baz'], ['foo' => $rule]);

        $this->assertTrue($validator->passes());
        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $rule->data);
    }

    public function testItCanAccessValidatorDuringValidation()
    {
        $trans = $this->getIlluminateArrayTranslator();

        $rule = new ValidationInvokableRuleTest_testItCanAccessValidatorDuringValidation_class();

        $validator = new Validator($trans, ['foo' => 'bar', 'bar' => 'baz'], ['foo' => $rule]);

        $this->assertTrue($validator->passes());
        $this->assertSame($validator, $rule->validator);
    }

    public function testItCanBeExplicit()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanBeExplicit_class();

        $validator = new Validator($trans, ['foo' => ''], ['foo' => $rule]);

        $this->assertTrue($validator->passes());
        $this->assertSame([], $validator->messages()->messages());
    }

    public function testItCanBeImplicit()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanBeImplicit_class();

        $validator = new Validator($trans, ['foo' => ''], ['foo' => $rule]);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'foo' => [
                'xxxx',
            ],
        ], $validator->messages()->messages());
    }

    public function testItIsExplicitByDefault()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItIsExplicitByDefault_class();

        $validator = new Validator($trans, ['foo' => ''], ['foo' => $rule]);

        $this->assertTrue($validator->passes());
        $this->assertSame([], $validator->messages()->messages());
    }

    public function testItThrowsIfTranslationIsNotFound()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItThrowsIfTranslationIsNotFound_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find translation [validation.key] for locale [en].');

        $validator->passes();
    }

    public function testItCanSpecifyTheValidationErrorKeyForTheErrorMessage()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new class() implements InvokableRule
        {
            public function __invoke($attribute, $value, $fail)
            {
                $fail('bar.baz', 'Another attribute error.');
                $fail('This attribute error.');
            }
        };

        $validator = new Validator($trans, ['foo' => 'xxxx'], ['foo' => $rule]);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'bar.baz' => [
                'Another attribute error.',
            ],
            'foo' => [
                'This attribute error.',
            ],
        ], $validator->messages()->messages());
    }

    private function getIlluminateArrayTranslator()
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }
}
