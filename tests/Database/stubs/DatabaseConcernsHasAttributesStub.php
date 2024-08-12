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

class HasAttributesWithArrayCast
{
    use HasAttributes;

    public function getArrayableAttributes(): array
    {
        return ['foo' => ''];
    }

    public function getCasts(): array
    {
        return ['foo' => 'array'];
    }

    public function usesTimestamps(): bool
    {
        return false;
    }
}
