<?php

namespace Illuminate\Container\Attributes;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Config implements ContextualAttribute
{
    public /*string */$key;
    public /*mixed */$default;

    /**
     * Create a new class instance.
     */
    public function __construct(/*public string */$key, /*public mixed */$default = null)
    {
        $this->key = backport_type_check('string', $key);
        $this->default = backport_type_check('mixed', $default);
    }

    /**
     * Resolve the configuration value.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('config')->get($attribute->key, $attribute->default);
    }
}
