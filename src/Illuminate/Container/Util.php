<?php

namespace Illuminate\Container;

use Closure;

/**
 * @internal
 */
class Util
{
    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * From Arr::wrap() in Illuminate\Support.
     *
     * @param  mixed  $value
     * @return array
     */
    public static function arrayWrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Return the default value of the given value.
     *
     * From global value() helper in Illuminate\Support.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function unwrapIfClosure($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * From Reflector::getParameterClassName() in Illuminate\Support.
     *
     * @param  \ReflectionParameter  $parameter
     * @return string|null
     */
    public static function getParameterClassName($parameter)
    {
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            $className = $parameter->getClass();

            if ($className && ($className = $className->getName())) {
                return in_array($className, ['array', 'callable'], true) ? null : $className;
            }

            return null;
        }

        $type = $parameter->getType();

        return ($type && ! $type->isBuiltin()) ? $type->getName() : null;
    }
}
