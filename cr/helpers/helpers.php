<?php

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
