<?php

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @template TValue
     *
     * @param  TValue  $value
     * @param  (callable(TValue): TValue)|null  $callback
     * @return TValue
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! \function_exists('_h_arr_get')) {
    function _h_arr_get(array $array, $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (isset($array[$segment])) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
