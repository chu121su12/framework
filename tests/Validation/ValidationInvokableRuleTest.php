<?php

namespace Illuminate\Tests\Validation;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\InvokableValidationRule;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationInvokableRuleTest_testItCanPass_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                //
            }
        }

class ValidationInvokableRuleTest_testItCanFail_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail("The {$attribute} attribute is not 'foo'. Got '{$value}' instead.");
            }
        }

class ValidationInvokableRuleTest_testItCanReturnMultipleErrorMessages_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('Error message 1.');
                $fail('Error message 2.');
            }
        }

class ValidationInvokableRuleTest_testItCanTranslateMessages_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('validation.translated-error')->translate();
            }
        }

class ValidationInvokableRuleTest_testItCanAccessDataDuringValidation_class implements InvokableRule, DataAwareRule
        {
            public $data = [];

            public function setData(array $data)
            {
                $this->data = $data;
            }

            public function __invoke($attribute, $value, Closure $fail)
            {
                if ($this->data === []) {
                    $fail('xxxx');
                }
            }
        }

class ValidationInvokableRuleTest_testItCanAccessValidatorDuringValidation_class implements InvokableRule, ValidatorAwareRule
        {
            public $validator = null;

            public function setValidator(ValidatorContract $validator)
            {
                $this->validator = $validator;
            }

            public function __invoke($attribute, $value, Closure $fail)
            {
                if ($this->validator === null) {
                    $fail('xxxx');
                }
            }
        }

class ValidationInvokableRuleTest_testItCanBeExplicit_class implements InvokableRule
        {
            public $implicit = false;

            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItCanBeImplicit_class implements InvokableRule
        {
            public $implicit = true;

            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItIsExplicitByDefault_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItCanSpecifyTheValidationErrorKeyForTheErrorMessage_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('bar.baz', 'Another attribute error.');
                $fail('This attribute error.');
            }
        }

class ValidationInvokableRuleTest_testItPerformsReplacementsWhenTranslating_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                if ($value !== null) {
                    $fail('validation.translated-error')->translate([
                        'baz' => 'xxxx',
                    ]);
                }
            }
        }

class ValidationInvokableRuleTest_testItLooksForLanguageFileCustomisations_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                if ($value !== null) {
                    $fail('validation.translated-error')->translate();
                }
            }
        }

class ValidationInvokableRuleTest_testItCanSpecifyLocaleWhenTranslating_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('validation.translated-error')->translate([], 'en');
                $fail('validation.translated-error')->translate([], 'fr');
            }
        }

class ValidationInvokableRuleTest_testItCanTranslateWithChoices_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('validation.translated-error')->translateChoice(2);
            }
        }

class ValidationInvokableRuleTest_testExplicitRuleCanUseInlineValidationMessages_class implements InvokableRule
        {
            public $implicit = false;

            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testImplicitRuleCanUseInlineValidationMessages_class implements InvokableRule
        {
            public $implicit = true;

            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
            }
        }

class ValidationInvokableRuleTest_testItCanReturnInvokableRule_class implements InvokableRule
        {
            public function __invoke($attribute, $value, Closure $fail)
            {
                $fail('xxxx');
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

    public function testItPerformsReplacementsWhenTranslating()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $trans->addLines(['validation.translated-error' => 'attribute: :attribute input: :input position: :position index: :index baz: :baz'], 'en');
        $rule = new ValidationInvokableRuleTest_testItPerformsReplacementsWhenTranslating_class();

        $validator = new Validator($trans, ['foo' => [null, 'bar']], ['foo.*' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo.1' => [
                'attribute: foo.1 input: bar position: 2 index: 1 baz: xxxx',
            ],
        ], $validator->messages()->messages());
    }

    public function testItLooksForLanguageFileCustomisations()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $trans->addLines(['validation.translated-error' => 'attribute: :attribute'], 'en');
        $trans->addLines(['validation.attributes.foo' => 'email address'], 'en');
        $rule = new ValidationInvokableRuleTest_testItLooksForLanguageFileCustomisations_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'attribute: email address',
            ],
        ], $validator->messages()->messages());
    }

    public function testItCanSpecifyLocaleWhenTranslating()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $trans->addLines(['validation.translated-error' => 'English'], 'en');
        $trans->addLines(['validation.translated-error' => 'French'], 'fr');
        $rule = new ValidationInvokableRuleTest_testItCanSpecifyLocaleWhenTranslating_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'English',
                'French',
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

    public function testItCanSpecifyTheValidationErrorKeyForTheErrorMessage()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testItCanSpecifyTheValidationErrorKeyForTheErrorMessage_class();

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

    public function testItCanTranslateWithChoices()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $trans->addLines(['validation.translated-error' => 'There is one error.|There are many errors.'], 'en');
        $rule = new ValidationInvokableRuleTest_testItCanTranslateWithChoices_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'There are many errors.',
            ],
        ], $validator->messages()->messages());
    }

    public function testExplicitRuleCanUseInlineValidationMessages()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testExplicitRuleCanUseInlineValidationMessages_class();

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule], [get_class($rule) => ':attribute custom.']);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'foo' => [
                'foo custom.',
            ],
        ], $validator->messages()->messages());

        $validator = new Validator($trans, ['foo' => 'bar'], ['foo' => $rule], ['foo.'.get_class($rule) => ':attribute custom with key.']);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'foo' => [
                'foo custom with key.',
            ],
        ], $validator->messages()->messages());
    }

    public function testImplicitRuleCanUseInlineValidationMessages()
    {
        $trans = $this->getIlluminateArrayTranslator();
        $rule = new ValidationInvokableRuleTest_testImplicitRuleCanUseInlineValidationMessages_class();

        $validator = new Validator($trans, ['foo' => ''], ['foo' => $rule], [get_class($rule) => ':attribute custom.']);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'foo' => [
                'foo custom.',
            ],
        ], $validator->messages()->messages());

        $validator = new Validator($trans, ['foo' => ''], ['foo' => $rule], ['foo.'.get_class($rule) => ':attribute custom with key.']);

        $this->assertFalse($validator->passes());
        $this->assertSame([
            'foo' => [
                'foo custom with key.',
            ],
        ], $validator->messages()->messages());
    }

    public function testItCanReturnInvokableRule()
    {
        $rule = new ValidationInvokableRuleTest_testItCanReturnInvokableRule_class();

        $invokableValidationRule = InvokableValidationRule::make($rule);

        $this->assertSame($rule, $invokableValidationRule->invokable());
    }

    private function getIlluminateArrayTranslator()
    {
        return new Translator(
            new ArrayLoader(),
            'en'
        );
    }
}
