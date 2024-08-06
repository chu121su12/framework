<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabaseConcernsHasAttributesTest extends TestCase
{
    /**
     * @requires PHP 8
     */
    public function testWithoutConstructor()
    {
        require_once __DIR__.'/stubs/DatabaseConcernsHasAttributesStub.php';

        $instance = new HasAttributesWithoutConstructor();
        $attributes = $instance->getMutatedAttributes();
        $this->assertEquals(['some_attribute'], $attributes);
    }

    /**
     * @requires PHP 8
     */
    public function testWithConstructorArguments()
    {
        require_once __DIR__.'/stubs/DatabaseConcernsHasAttributesStub.php';

        $instance = new HasAttributesWithConstructorArguments(null);
        $attributes = $instance->getMutatedAttributes();
        $this->assertEquals(['some_attribute'], $attributes);
    }

    /**
     * @requires PHP 8
     */
    public function testRelationsToArray()
    {
        $mock = m::mock(HasAttributesWithoutConstructor::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getArrayableRelations')->andReturn([
                'arrayable_relation' => Collection::make(['foo' => 'bar']),
                'invalid_relation' => 'invalid',
                'null_relation' => null,
            ])
            ->getMock();

        $this->assertEquals([
            'arrayable_relation' => ['foo' => 'bar'],
            'null_relation' => null,
        ], $mock->relationsToArray());
    }
}
