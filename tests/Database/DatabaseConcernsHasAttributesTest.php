<?php

namespace Illuminate\Tests\Database;

use PHPUnit\Framework\TestCase;

class DatabaseConcernsHasAttributesTest extends TestCase
{
    /**
     * @requires PHP 7
     */
    public function testWithoutConstructor()
    {
        require_once __DIR__.'/stubs/DatabaseConcernsHasAttributesStub.php';

        $instance = new HasAttributesWithoutConstructor();
        $attributes = $instance->getMutatedAttributes();
        $this->assertEquals(['some_attribute'], $attributes);
    }

    /**
     * @requires PHP 7
     */
    public function testWithConstructorArguments()
    {
        require_once __DIR__.'/stubs/DatabaseConcernsHasAttributesStub.php';

        $instance = new HasAttributesWithConstructorArguments(null);
        $attributes = $instance->getMutatedAttributes();
        $this->assertEquals(['some_attribute'], $attributes);
    }
}
