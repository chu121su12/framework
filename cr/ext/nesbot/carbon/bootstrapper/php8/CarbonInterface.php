<?php

namespace Carbon\Patch;

interface CarbonInterface
{
    /**
     * Returns the values to dump on serialize() called on.
     *
     * Only used by PHP >= 7.4.
     *
     * @return array
     */
    public function __serialize(): array;

    /**
     * Set locale if specified on unserialize() called.
     *
     * Only used by PHP >= 7.4.
     *
     * @return void
     */
    public function __unserialize(array $data): void;
}
