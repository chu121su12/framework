<?php

namespace Carbon\Patch;

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
