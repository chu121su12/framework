<?php

namespace Carbon\Patch;

if (version_compare(PHP_VERSION, '7.0.0', '<'))
{
    trait SetState
    {
        /**
         * The __set_state handler.
         *
         * @param string|array $dump
         *
         * @return static
         */
        public static function __set_state()
        {
            return static::__set_state_(...func_get_args());
        }
    }
}
else
{
    trait SetState
    {
        /**
         * The __set_state handler.
         *
         * @param string|array $dump
         *
         * @return static
         */
        public static function __set_state($dump)
        {
            return static::__set_state_(...func_get_args());
        }
    }
}
