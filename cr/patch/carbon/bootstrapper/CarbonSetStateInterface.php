<?php

namespace Carbon\Patch;

use ReturnTypeWillChange;

interface CarbonSetStateInterface
{
    /**
     * The __set_state handler.
     *
     * @param string|array $dump
     *
     * @return static
     */
    #[ReturnTypeWillChange]
    public static function __set_state();
}
