<?php

namespace Carbon\Patch;

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