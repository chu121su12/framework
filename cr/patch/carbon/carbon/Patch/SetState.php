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
            list($dump) = func_get_args();

            if (is_string($dump)) {
                return static::parse($dump);
            }

            /** @var \DateTimeInterface $date */
            $date = get_parent_class(static::class) && method_exists(parent::class, '__set_state')
                ? parent::__set_state((array) $dump)
                : (object) $dump;

            return static::instance($date);
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
            if (is_string($dump)) {
                return static::parse($dump);
            }

            /** @var \DateTimeInterface $date */
            $date = get_parent_class(static::class) && method_exists(parent::class, '__set_state')
                ? parent::__set_state((array) $dump)
                : (object) $dump;

            return static::instance($date);
        }
    }
}