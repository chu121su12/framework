<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Validation\Rule;
use PHPUnit\Framework\TestCase;

if (PHP_VERSION_ID >= 80100) {
    include_once 'Enums.php';
}

class ValidationArrayRuleTest extends TestCase
{
    public function testItCorrectlyFormatsAStringVersionOfTheRule()
    {
        $rule = Rule::array_();

        $this->assertSame('array', (string) $rule);

        $rule = Rule::array_('key_1', 'key_2', 'key_3');

        $this->assertSame('array:key_1,key_2,key_3', (string) $rule);

        $rule = Rule::array_(['key_1', 'key_2', 'key_3']);

        $this->assertSame('array:key_1,key_2,key_3', (string) $rule);

        $rule = Rule::array_(collect(['key_1', 'key_2', 'key_3']));

        $this->assertSame('array:key_1,key_2,key_3', (string) $rule);
    }

    /**
     * @requires PHP 8.1
     */
    public function testItCorrectlyFormatsAStringVersionOfTheRuleEnum()
    {
        $rule = Rule::array_([ArrayKeys::key_1, ArrayKeys::key_2, ArrayKeys::key_3]);

        $this->assertSame('array:key_1,key_2,key_3', (string) $rule);

        $rule = Rule::array_([ArrayKeysBacked::key_1, ArrayKeysBacked::key_2, ArrayKeysBacked::key_3]);

        $this->assertSame('array:key_1,key_2,key_3', (string) $rule);
    }
}
