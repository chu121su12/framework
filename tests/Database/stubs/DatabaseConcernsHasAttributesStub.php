<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class HasAttributesWithoutConstructor
{
    use HasAttributes;

    public function someAttribute(): Attribute
    {
        return new Attribute(function () {
        });
    }
}

class HasAttributesWithConstructorArguments extends HasAttributesWithoutConstructor
{
    public function __construct($someValue)
    {
    }
}
