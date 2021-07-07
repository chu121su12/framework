<?php

if (! \class_exists('TypeError')) {
    class TypeError extends Error
    {
    }
}

if (! \function_exists('is_iterable')) {
    function is_iterable($obj)
    {
        return \is_array($obj)
            || (\is_object($obj)
                && ($obj instanceof \Traversable));
    }
}

if (! \function_exists('cast_to_array')) {
    function cast_to_array($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_array($value)) {
            return $value;
        }

        if (! $strict) {
            return (array) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_bool')) {
    function cast_to_bool($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_bool($value)) {
            return $value;
        }

        if (! $strict) {
            return (bool) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_callable')) {
    function cast_to_callable($value, $default = null)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_callable($value)) {
            return $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_float')) {
    function cast_to_float($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_float($value)) {
            return $value;
        }

        if (! $strict) {
            return (float) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_int')) {
    function cast_to_int($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_int($value)) {
            return $value;
        }

        if (! $strict) {
            return (int) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_iterable')) {
    function cast_to_iterable($value, $default = null)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_iterable($value)) {
            return $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_object')) {
    function cast_to_object($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_object($value)) {
            return $value;
        }

        if (! $strict) {
            return (object) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_string')) {
    function cast_to_string($value, $default = null, $strict = false)
    {
        if (\func_num_args() > 1 && null === $value) {
            return $default;
        }

        if (\is_string($value)) {
            return $value;
        }

        if (! $strict) {
            return (string) $value;
        }

        throw new TypeError;
    }
}

if (! \function_exists('cast_to_bools')) {
    function cast_to_bools($strict/* = false*/, array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_bool($value, null, $strict);
        }, $values);
    }
}

if (! \function_exists('cast_to_callables')) {
    function cast_to_callables(array $values)
    {
        return \array_map(function ($value) {
            return cast_to_callable($value);
        }, $values);
    }
}

if (! \function_exists('cast_to_floats')) {
    function cast_to_floats($strict/* = false*/, array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_float($value, null, $strict);
        }, $values);
    }
}

if (! \function_exists('cast_to_ints')) {
    function cast_to_ints($strict/* = false*/, array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_int($value, null, $strict);
        }, $values);
    }
}

if (! \function_exists('cast_to_iterables')) {
    function cast_to_iterables(array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_iterable($value);
        }, $values);
    }
}

if (! \function_exists('cast_to_objects')) {
    function cast_to_objects($strict/* = false*/, array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_object($value, null, $strict);
        }, $values);
    }
}

if (! \function_exists('cast_to_strings')) {
    function cast_to_strings($strict/* = false*/, array $values)
    {
        return \array_map(function ($value) use ($strict) {
            return cast_to_string($value, null, $strict);
        }, $values);
    }
}
