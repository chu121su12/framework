<?php

use desktopd\SHA3\Sponge as SHA3;
use Laravel\SerializableClosure\SerializableClosure;
use Opis\Closure\SerializableClosure as OpisSerializableClosure;

if (! \function_exists('backport_instanceof_throwable')) {
    function backport_instanceof_throwable($any)
    {
        if (\class_exists('Throwable')) {
            return $any instanceof Throwable;
        }

        return $any instanceof Error || $any instanceof Exception;
    }
}

if (! \function_exists('backport_type_throwable')) {
    function backport_type_throwable($any, $null = null)
    {
        if (\func_num_args() === 2 && null === $any) {
            return;
        }

        if ((\class_exists('Throwable') && !($any instanceof Throwable))
            || (!($any instanceof Error) && !($any instanceof Exception))) {
            throw new TypeError;
        }
    }
}

if (! \function_exists('backport_match')) {
    define('__BACKPORT_MATCH_DEFAULT_CASE__', '__BACKPORT_MATCH_DEFAULT_CASE__');

    function backport_match($matchValue, ...$matchArms)
    {
        $matchValue = value($matchValue);

        $hasDefault = false;

        foreach ($matchArms as $arms) {
            $expression = \array_pop($arms);

            foreach ($arms as $arm) {
                if ($arm === __BACKPORT_MATCH_DEFAULT_CASE__) {
                    if ($hasDefault) {
                        \trigger_error('Fatal error', \E_USER_ERROR);
                        throw new Exception('Fatal error');
                    }

                    $hasDefault = true;
                }

                if ($hasDefault || value($arm) === $matchValue) {
                    return value($expression);
                }
            }
        }

        throw \class_exists('UnhandledMatchError')
            ? new UnhandledMatchError
            : new Exception;
    }
}

if (! \function_exists('backport_json_decode')) {
    function backport_json_decode($json, $assoc = false, $depth = 512, $options = 0, $throw = false)
    {
        if (\version_compare(\PHP_VERSION, '7.3', '>=')) {
            return \json_decode($json, $assoc, $depth, $throw ? ($options | JSON_THROW_ON_ERROR) : $options);
        }

        // https://www.php.net/manual/en/function.json-decode 7.0.0 changes
        $result = \version_compare(\PHP_VERSION, '7.0', '<') && (string) $json === ''
            ? \json_decode('-', $assoc, $depth, $options)
            : \json_decode($json, $assoc, $depth, $options);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $result;
        }

        if (! $throw) {
            return null;
        }

        $jsonErrorMsg = \json_last_error_msg();
        \json_encode(''); // reset error // NOT \json_decode() !
        throw new \JsonException($jsonErrorMsg);
    }
}

if (! \function_exists('backport_json_encode')) {
    function backport_json_encode($value, $options = 0, $depth = 512, $throw = false)
    {
        if (\version_compare(\PHP_VERSION, '7.3', '>=')) {
            return \json_encode($value, $throw ? ($options | JSON_THROW_ON_ERROR) : $options, $depth);
        }

        $output = \json_encode($value, $options, $depth);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $output;
        }

        if (! $throw) {
            return false;
        }

        $jsonErrorMsg = \json_last_error_msg();
        \json_encode(''); // reset error
        throw new \JsonException($jsonErrorMsg);
    }
}

if (! \function_exists('backport_substr_count')) {
    function backport_substr_count($haystack, $needle, $offset = 0, $length = null)
    {
        if (\version_compare(\PHP_VERSION, '7.1', '<')) {
            if ($offset < 0) {
                $offset = -$offset - 1;
                $haystack = \strrev($haystack);
                $needle = \strrev($needle);
            }

            if (null !== $length) {
                if ($length < 0) {
                    $length = \strlen($haystack) + $length - $offset;
                }

                return \substr_count($haystack, $needle, $offset, $length);
            }

            return \substr_count($haystack, $needle, $offset);
        }

        if (null !== $length) {
            return \substr_count($haystack, $needle, $offset, $length);
        }

        return \substr_count($haystack, $needle, $offset);
    }
}

if (! \function_exists('backport_bcadd')) {
    function backport_bcadd($num1, $num2, $scale = null)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            if (! \is_numeric($num1) || (\func_num_args() === 3 && \is_string($num1) && \str_contains($num1, 'e'))) {
                throw new ValueError('backport: bcadd(): bcmath function argument is not well-formed');
            }
        }

        return \bcadd(...func_get_args());
    }
}

if (! \function_exists('backport_bcmod')) {
    function backport_bcmod($dividend, $divisor, $scale)
    {
        if (\version_compare(\PHP_VERSION, '7.2', '<') && (new ReflectionFunction('bcmod'))->getNumberOfParameters() === 2) {
            $currentScale = \strlen(\bcsqrt('2')) - 2;
            \bcscale($scale);
            $modulo = \bcsub($dividend, \bcmul(\bcdiv($dividend, $divisor, 0), $divisor));
            \bcscale($currentScale);

            return $modulo;
        }

        return \bcmod($dividend, $divisor, $scale);
    }
}

if (! \function_exists('backport_spaceship_operator')) {
    function backport_spaceship_operator($left, $right) // <=>
    {
        if ($left > $right) {
            return 1;
        }

        if ($left < $right) {
            return -1;
        }

        return 0;
    }
}

if (! \function_exists('backport_string_offset')) {
    function backport_string_offset($string, $offset) // ex: $string[-1]
    {
        if ($offset >= 0 || \version_compare(\PHP_VERSION, '7.1', '>=')) {
            return $string[$offset];
        }

        return \substr($string, $offset, 1);
    }
}

if (! \function_exists('backport_reflection_type_cast_string')) {
    function backport_reflection_type_cast_string(ReflectionType $type)
    {
        if (\version_compare(\PHP_VERSION, '7.1', '<')) {
            return (string) $type;
        }

        if (\version_compare(\PHP_VERSION, '8.0', '>=') && $type instanceof ReflectionUnionType) {
            return 'mixed';
        }

        return $type->getName();
    }
}

if (! \function_exists('backport_only_reflection_parameter_get_type')) {
    function backport_only_reflection_parameter_get_type(ReflectionParameter $parameter)
    {
        $className = $parameter->getClass();

        if ($className && ($className = $className->getName())) {
            return \in_array($className, ['array', 'callable', 'self'], true) ? null : $className;
        }

        return null;
    }
}

if (! \function_exists('backport_reflection_parameter_get_class')) {
    function backport_reflection_parameter_get_class(ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->getClass();
        }

        return $parameter->getType() && ! $parameter->getType()->isBuiltin()
            ? new ReflectionClass($parameter->getType()->getName())
            : null;
    }
}

if (! \function_exists('backport_reflection_parameter_declares_array')) {
    function backport_reflection_parameter_declares_array(ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->isArray();
        }

        // return $parameter->getType() && $parameter->getType()->getName() === 'array';

        return \in_array(
            'array',
            \array_map(
                function ($t) {
                    return $t->getName();
                },
                $parameter instanceof ReflectionUnionType
                    ? $parameter->getTypes()
                    : [$parameter]
                ),
            true
        );
    }
}

if (! \function_exists('backport_reflection_parameter_declares_callable')) {
    function backport_reflection_parameter_declares_callable(ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->isCallable();
        }

        // return $parameter->getType() && $parameter->getType()->getName() === 'callable';

        return \in_array(
            'callable',
            \array_map(
                function ($t) {
                    return $t->getName();
                },
                $parameter instanceof \ReflectionUnionType
                    ? $parameter->getTypes()
                    : [$parameter]
                ),
            true
        );
    }
}

if (! \function_exists('backport_reflection_parameter_first_classable')) {
    function backport_reflection_parameter_first_classable(ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->getClass();
        }

        foreach ($parameter instanceof ReflectionUnionType
            ? $parameter->getTypes()
            : [$parameter] as $type) {
            // return $type;
        }
    }
}

if (! \function_exists('backport_closure_from_callable')) {
    /* Example use:

        if (version_compare(PHP_VERSION, '7.1', '<')) {
            return backport_closure_from_callable([$this, $methodName]);
        } else {
            return Closure::fromCallable([$this, $methodName]);
        }

        if (\version_compare(\PHP_VERSION, '7.1', '<')) {
            return backport_closure_from_callable(function () {});
        } else {
            return Closure::fromCallable(function () {});
        }
    */

    function backport_closure_from_callable($callable)
    {
        if (\version_compare(\PHP_VERSION, '7.1', '>=')) {
            return Closure::fromCallable($callable);
        }

        if (\is_array($callable)) {
            return (new ReflectionMethod(...$callable))->getClosure($callable[0]);
        }

        if (\is_object($callable) && \method_exists($callable, '__invoke')) {
            return (new ReflectionMethod($callable, '__invoke'))->getClosure($callable);
        }

        return function () use ($callable) {
            return \call_user_func_array($callable, \func_get_args());
        };
    }
}

if (! \function_exists('backport_call_callable')) {
    function backport_call_callable($callback, &$value)
    {
        if (is_callable($callback)) {
            return $callback($value);
        }

        if (\is_object($callback)) {
            throw new Error(\sprintf('Object of type %s is not callable', \get_class($callback)));
        }

        throw new Error(\sprintf('Call to undefined function %s()', $callback));
    }
}

if (! \function_exists('backport_named_arguments')) {
    function backport_named_arguments($placeholders, array $arguments)
    {
        $keys = array_keys($arguments);
        if (array_keys($keys) === $keys) {
            return $arguments;
        }

        $sortedArguments = [];

        foreach ($placeholders as $key => $default) {
            $sortedArguments[$key] = \array_key_exists($key, $arguments) 
                ? $arguments[$key] 
                : $default;
        }

        return \array_values($sortedArguments);
    }
}

if (! \function_exists('backport_function_call_able')) {
    class BackportInternalFunctionCallAble
    {
        private $closure;

        public function __construct(Closure $closure)
        {
            $this->closure = $closure;
        }

        public function call($newThis)
        {
            $bound = $this->closure->bindTo($newThis, $newThis);

            return $bound();
        }
    }

    function backport_function_call_able($closure)
    {
        return new BackportInternalFunctionCallAble($closure);
    }
}

if (! \function_exists('backport_convert_error_to_error_exception')) {
    function backport_convert_error_to_error_exception()
    {
        \set_error_handler(function($errno, $errstr, $errfile, $errline ) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        return function () {
            \restore_error_handler();
        };
    }
}

if (! \function_exists('backport_hash_file')) {
    function backport_hash_file($algorithm, $path)
    {
        switch (\strtolower($algorithm)) {
            case 'sha3-256':
                return with(SHA3::init(SHA3::SHA3_256), function ($sponge) use ($path) {
                    $sponge->absorb(\file_get_contents($path));
                    return \bin2hex($sponge->squeeze());
                });

            default:
                return \hash_file($algorithm, $path);
        }
    }
}

if (! \function_exists('backport_abstract_error_message')) {
    function backport_abstract_error_message($class, $method)
    {
        return \sprintf(
            'Class %s contains abstract method %s and must therefore be declared abstract or implement the remaining methods',
            $class,
            $method
        );
    }
}

if (! \function_exists('backport_type_assert')) {
    function backport_type_assert($nullable, $type, $value)
    {
        if ($value === null) {
            return (bool) $nullable;
        }

        switch ($type) {
            case 'array': return \is_array($value);
            case 'false': return $value === false;
            case 'float': return \is_float($value);
            case 'int': return \is_int($value);
            case 'mixed': return ! \is_object($value);
            case 'null': return $value === null;
            case 'string': return \is_string($value);
            case 'stdObject': return \is_object($value);
            case 'true': return $value === true;
            default: return $value instanceof $type;
        }
    }
}

if (! \function_exists('backport_type_check')) {
    function backport_type_check($types, $value, $strict = false)
    {
        if (\is_string($types)) {
            $types = \explode('|', $types);
        }

        foreach ($types as $type) {
            $nullable = \substr($type, 0, 1) === '?';

            if (backport_type_assert($nullable, (string) ($nullable ? \substr($type, 1) : $type), $value)) {
                return $value;
            }
        }

        if ($strict) {
            $label = \is_object($value) ? \get_class($value) : (\gettype($value) . " ($value)");

            throw new TypeError("Found value of {$label}; expected " . \implode('|', $types));
        }

        return $value;
    }
}

if (! \function_exists('backport_array_type_check')) {
    function backport_array_type_check($types, $values, $strict = false)
    {
        if (\is_string($types)) {
            $types = \explode('|', $types);
        }

        foreach ($values as $key => $value) {
            $values[$key] = backport_type_check($types, $value, $strict);
        }

        return $values;
    }
}

if (! \function_exists('backport_call_named_args')) {
    function backport_call_named_args(array $target, array $arguments, $callback)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '>=') || \array_is_list($arguments)) {
            return $callback($arguments);
        }

        list($classOrObject, $methodName) = $target;

        $unmatchedArguments = [];
        $matchedArguments = [];

        foreach ((new ReflectionClass($classOrObject))->getMethod($methodName)->getParameters() as $param) {
            $name = $param->getName();
            if (\array_key_exists($name, $arguments)) {
                $matchedArguments[] = $arguments[$name];
            }
            else {
                $unmatchedArguments[] = $arguments[$name];
            }
        }

        return $callback(\array_merge($matchedArguments, $unmatchedArguments));
    }
}

if (! \function_exists('backport_serialize')) {
    function backport_serialize($object)
    {
        if (\version_compare(\PHP_VERSION, '7.4', '>=') || $object instanceof SerializableClosure || $object instanceof OpisSerializableClosure) {
            return serialize($object);
        }

        if (\method_exists($object, '__serialize')) {
            $custom = $object->__serialize();

            if (! \is_array($custom)) {
                throw new TypeError;
            }

            return serialize([
                "['¯\_(ツ)_/¯']" => [
                    get_class($object),
                    $custom,
                ],
            ]);
        }

        return serialize($object); // maybe __sleep();
    }
}

if (! \function_exists('backport_unserialize')) {
    function backport_unserialize($serialized)
    {
        if (\version_compare(\PHP_VERSION, '7.4', '>=')) {
            return unserialize($serialized);
        }

        $unserialized = unserialize($serialized);

        if (! (\is_array($unserialized) && \array_key_exists("['¯\_(ツ)_/¯']", $unserialized) && count($unserialized) === 1)) {
            return $unserialized;
        }

        list($class, $custom) = $unserialized["['¯\_(ツ)_/¯']"];

        // if ($class === SerializableClosure::class || $class === OpisSerializableClosure::class) {
        //     return $unserialized;
        // }

        if (\method_exists($class, '__unserialize')) {
            $object = (new ReflectionClass($class))->newInstanceWithoutConstructor();

            if ($object->__unserialize($custom) === null) {
                return $object;
            }

            throw new TypeError;
        }

        return $unserialized;
    }
}

if (! \function_exists('backport_is_numeric')) {
    function backport_is_numeric($value)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '>=')) {
            return \is_numeric($value);
        }

        if (! \is_string($value)) {
            return \is_numeric($value);
        }

        return \is_numeric(\rtrim($value));
    }
}
