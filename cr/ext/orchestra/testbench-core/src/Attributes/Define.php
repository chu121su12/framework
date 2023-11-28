<?php

namespace Orchestra\Testbench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Define
{
    public /*string */$group;
    public /*string */$method;

    /**
     * Construct a new attribute.
     *
     * @param  string  $group
     * @param  string  $method
     */
    public function __construct(
        /*public string */$group,
        /*public string */$method
    ) {
        $this->group = backport_type_check('string', $group);
        $this->method = backport_type_check('string', $method);

        //
    }

    /**
     * Resolve the actual attribute class.
     *
     * @return object|null
     */
    public function resolve()/*: ?object*/
    {
        switch (strtolower($this->group)) {
            case 'env':
                return new DefineEnvironment($this->method);
            case 'db':
                return new DefineDatabase($this->method);
            case 'route':
                return new DefineRoute($this->method);
            default:
                return null;
        }
    }
}
