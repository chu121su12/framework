<?php

namespace Carbon\Patch;

use ReturnTypeWillChange;

trait SetState
{
    /**
     * The __set_state handler.
     *
     * @param string|array $dump
     *
     * @return static
     */
    #[ReturnTypeWillChange]
    public static function __set_state()
    {
        return static::__set_state_(...\func_get_args());
    }
}
