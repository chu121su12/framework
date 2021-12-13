<?php

if (! \function_exists('backport_instanceof_throwable')) {
    function backport_instanceof_throwable($any)
    {
        if (class_exists('Throwable')) {
            return $any instanceof \Throwable;
        }

        return $any instanceof \Error || $any instanceof \Exception;
    }
}

if (! \function_exists('backport_type_throwable')) {
    function backport_type_throwable($any, $null = null)
    {
        if (\func_num_args() === 2 && null === $any) {
            return;
        }

        if ((class_exists('Throwable') && !($any instanceof \Throwable))
            || (!($any instanceof \Error) && !($any instanceof \Exception))) {
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
                        throw new \Exception('Fatal error');
                    }

                    $hasDefault = true;
                }

                if ($hasDefault || value($arm) === $matchValue) {
                    return value($expression);
                }
            }
        }

        throw \class_exists('UnhandledMatchError')
            ? new \UnhandledMatchError
            : new \Exception;
    }
}

if (! \function_exists('backport_json_decode')) {
    function backport_json_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        // https://www.php.net/manual/en/function.json-decode 7.0.0 changes
        if (\version_compare(\PHP_VERSION, '7.0', '<') && (string) $json === '') {
            return \json_decode('-', $assoc, $depth, $options);
        }

        return \json_decode($json, $assoc, $depth, $options);
    }
}

if (! \function_exists('backport_json_decode_throw')) {
    function backport_json_decode_throw($json, $assoc = false, $depth = 512, $options = 0)
    {
        $result = backport_json_decode($json, $assoc, $depth, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \JsonException(json_last_error_msg());
        }

        return $result;
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

if (! \function_exists('backport_bcmod')) {
    function backport_bcmod($dividend, $divisor, $scale)
    {
        if (\version_compare(\PHP_VERSION, '7.2', '<') && (new \ReflectionFunction('bcmod'))->getNumberOfParameters() === 2) {
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
    function backport_reflection_type_cast_string(\ReflectionType $type)
    {
        if (\version_compare(\PHP_VERSION, '7.1', '<')) {
            return (string) $type;
        }

        if (\version_compare(\PHP_VERSION, '8.0', '>=') && $type instanceof \ReflectionUnionType) {
            return 'mixed';
        }

        return $type->getName();
    }
}

if (! \function_exists('backport_only_reflection_parameter_get_type')) {
    function backport_only_reflection_parameter_get_type(\ReflectionParameter $parameter)
    {
        $className = $parameter->getClass();

        if ($className && ($className = $className->getName())) {
            return in_array($className, ['array', 'callable', 'self'], true) ? null : $className;
        }

        return null;
    }
}

if (! \function_exists('backport_reflection_parameter_get_class')) {
    function backport_reflection_parameter_get_class(\ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->getClass();
        }

        return $parameter->getType() && ! $parameter->getType()->isBuiltin()
            ? new \ReflectionClass($parameter->getType()->getName())
            : null;
    }
}

if (! \function_exists('backport_reflection_parameter_declares_array')) {
    function backport_reflection_parameter_declares_array(\ReflectionParameter $parameter)
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
                $parameter instanceof \ReflectionUnionType
                    ? $parameter->getTypes()
                    : [$parameter]
                ),
            true
        );
    }
}

if (! \function_exists('backport_reflection_parameter_declares_callable')) {
    function backport_reflection_parameter_declares_callable(\ReflectionParameter $parameter)
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
    function backport_reflection_parameter_first_classable(\ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '8.0', '<')) {
            return $parameter->getClass();
        }

        foreach ($parameter instanceof \ReflectionUnionType
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
            return \Closure::fromCallable($callable);
        }

        if (is_array($callable)) {
            return (new \ReflectionMethod(...$callable))->getClosure($callable[0]);
        }

        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return (new \ReflectionMethod($callable, '__invoke'))->getClosure($callable);
        }

        return function () use ($callable) {
            return call_user_func_array($callable, func_get_args());
        };
    }
}
