<?php

namespace Illuminate\Routing;

use Illuminate\Support\Reflector;
use Illuminate\Support\Str;
use ReflectionFunction;
use ReflectionMethod;

class RouteSignatureParameters
{
    /**
     * Extract the route action's signature parameters.
     *
     * @param  array  $action
     * @param  array  $conditions
     * @return array
     */
    public static function fromAction(array $action, $conditions = [])
    {
        $callback = RouteAction::containsSerializedClosure($action)
                        ? unserialize($action['uses'])->getClosure()
                        : $action['uses'];

        $parameters = is_string($callback)
                        ? static::fromClassMethodString($callback)
                        : (new ReflectionFunction($callback))->getParameters();

        return backport_match(true,
            [! empty($conditions['subClass']), function () use ($conditions, $parameters) {
                return array_filter($parameters, function ($p) use ($conditions) { return Reflector::isParameterSubclassOf($p, $conditions['subClass']);});
            }],
            [! empty($conditions['backedEnum']), function () use ($parameters) {
                return array_filter($parameters, function ($p) { return Reflector::isParameterBackedEnumWithStringBackingType($p); });
            }],
            [__BACKPORT_MATCH_DEFAULT_CASE__, $parameters]
        );
    }

    /**
     * Get the parameters for the given class / method by string.
     *
     * @param  string  $uses
     * @return array
     */
    protected static function fromClassMethodString($uses)
    {
        list($class, $method) = Str::parseCallback($uses);

        if (! method_exists($class, $method) && Reflector::isCallable($class, $method)) {
            return [];
        }

        return (new ReflectionMethod($class, $method))->getParameters();
    }
}
