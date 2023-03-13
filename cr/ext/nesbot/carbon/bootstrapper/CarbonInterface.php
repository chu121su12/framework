<?php

namespace Carbon\Patch;

use ReturnTypeWillChange;

interface CarbonInterface
{
    /**
     * Returns the values to dump on serialize() called on.
     *
     * Only used by PHP >= 7.4.
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function __serialize()/*: array*/;

    /**
     * Set locale if specified on unserialize() called.
     *
     * Only used by PHP >= 7.4.
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function __unserialize(array $data)/*: void*/;
}
