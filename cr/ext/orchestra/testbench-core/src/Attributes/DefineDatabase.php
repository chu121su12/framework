<?php

namespace Orchestra\Testbench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DefineDatabase
{
    public /*string */$method;

    /**
     * Construct a new attribute.
     *
     * @param  string  $method
     */
    public function __construct(
        /*public string */$method
    ) {
        $this->method = backport_type_check('string', $method);

        //
    }
}
