<?php

namespace Carbon;

if (version_compare(PHP_VERSION, '7.0.0', '<'))
{
    interface CarbonSetStateInterface
    {
        /**
         * The __set_state handler.
         *
         * @param string|array $dump
         *
         * @return static
         */
        public static function __set_state();
    }
}
else
{
    interface CarbonSetStateInterface
    {
        /**
         * The __set_state handler.
         *
         * @param string|array $dump
         *
         * @return static
         */
        public static function __set_state($dump);
    }
}