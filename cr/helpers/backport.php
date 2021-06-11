<?php

if (! \function_exists('backport_match')) {
    function backport_match($matchValue, ...$matchArms)
    {
        $matchValue = value($matchValue);

        $hasDefault = false;

        foreach ($matchArms as $arms) {
            $expression = \array_pop($arms);

            foreach ($arms as $key => $arm) {
                if ($key === 'default' && $arm === null) {
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
        if ((string) $json === '') {
            return \json_decode('-');
        }

        return \json_decode($json, $assoc, $depth, $options);
    }
}

if (! \function_exists('backport_substr_count')) {
    function backport_substr_count($haystack, $needle, $offset = 0, $length = null)
    {
        if (\version_compare(\PHP_VERSION, '7.1.0', '<')) {
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
        if (\version_compare(\PHP_VERSION, '7.2.0', '<') && (new \ReflectionFunction('bcmod'))->getNumberOfParameters() === 2) {
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
        if ($offset >= 0) {
            return $string[0];
        }

        return \substr($string, $offset, 1);
    }
}

if (! \function_exists('backport_reflection_type_cast_string')) {
    function backport_reflection_type_cast_string(\ReflectionType $type)
    {
        if (\version_compare(\PHP_VERSION, '7.1.0', '<')) {
            return (string) $type;
        }

        if (\version_compare(\PHP_VERSION, '8.0', '>=') && $type instanceof \ReflectionUnionType) {
            return 'mixed';
        }

        return $type->getName();
    }
}

if (! \function_exists('backport_reflection_parameter_get_class')) {
    function backport_reflection_parameter_get_class(\ReflectionParameter $parameter)
    {
        if (\version_compare(\PHP_VERSION, '7.9', '<=')) {
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
        if (\version_compare(\PHP_VERSION, '7.9', '<=')) {
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
        if (\version_compare(\PHP_VERSION, '7.9', '<=')) {
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
        if (\version_compare(\PHP_VERSION, '7.9', '<=')) {
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

        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            return backport_closure_from_callable($this, [$this, $methodName]);
        } else {
            return Closure::fromCallable([$this, $methodName]);
        }

        if (\version_compare(\PHP_VERSION, '7.0.0', '<')) {
            return backport_closure_from_callable(new static, function () {});
        } else {
            return Closure::fromCallable(function () {});
        }
    */

    function backport_closure_from_callable($callingThis, $callable)
    {
        if (! \version_compare(\PHP_VERSION, '7.0.0', '<')) {
            throw new \Exception('Use \Closure::fromCallable() directly from calling method.');
        }

        if (is_array($callable)) {
            return (new \ReflectionMethod(...$callable))->getClosure($callingThis);
        }

        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return (new \ReflectionMethod($callable, '__invoke'))->getClosure($callable);
        }

        return function () use ($callable) {
            return call_user_func_array($callable, func_get_args());
        };
    }
}
