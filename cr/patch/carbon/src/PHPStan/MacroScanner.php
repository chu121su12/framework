<?php

namespace Carbon\PHPStan;

use Carbon\CarbonInterface;
use ReflectionClass;
use ReflectionException;

final class MacroScanner
{
    /**
     * Return true if the given pair class-method is a Carbon macro.
     *
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @param string $methodName
     *
     * @return bool
     */
    public function hasMethod($className, $methodName) //// bool
    {
        $methodName = cast_to_string($methodName);

        $className = cast_to_string($className);

        return is_a($className, CarbonInterface::class, true) &&
            \is_callable([$className, 'hasMacro']) &&
            $className::hasMacro($methodName);
    }

    /**
     * Return the Macro for a given pair class-method.
     *
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @param string $methodName
     *
     * @throws ReflectionException
     *
     * @return Macro
     */
    public function getMethod($className, $methodName) // Macro
    {
        $methodName = cast_to_string($methodName);

        $className = cast_to_string($className);

        $reflectionClass = new ReflectionClass($className);
        $property = $reflectionClass->getProperty('globalMacros');

        $property->setAccessible(true);
        $macro = $property->getValue()[$methodName];

        return new Macro(
            $className,
            $methodName,
            $macro
        );
    }
}
