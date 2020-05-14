<?php

namespace CR\LaravelBackport;

class Cast
{
    public static function _array($value, $default = null)
    {
        if (func_num_args() === 2 && is_null($value)) {
            return $default;
        }

        return (array) $value;
    }

    public static function _bool($value, $default = null)
    {
        if (func_num_args() === 2 && is_null($value)) {
            return $default;
        }

        return (bool) $value;
    }

    public static function _float($value, $default = null)
    {
        if (func_num_args() === 2 && is_null($value)) {
            return $default;
        }

        return (float) $value;
    }

    public static function _int($value, $default = null)
    {
        if (func_num_args() === 2 && is_null($value)) {
            return $default;
        }

        return (int) $value;
    }

    public static function _string($value, $default = null)
    {
        if (func_num_args() === 2 && is_null($value)) {
            return $default;
        }

        return (string) $value;
    }
}
