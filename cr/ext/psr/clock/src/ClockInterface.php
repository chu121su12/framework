<?php

namespace Psr\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    /**
     * Returns the current time as a DateTimeImmutable Object
     */
    #[\ReturnTypeWillChange]
    public function now()/*: DateTimeImmutable*/;
}
