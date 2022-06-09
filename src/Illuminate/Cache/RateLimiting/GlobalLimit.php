<?php

namespace Illuminate\Cache\RateLimiting;

class GlobalLimit extends Limit
{
    /**
     * Create a new limit instance.
     *
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return void
     */
    public function __construct(/*int */$maxAttempts, /*int */$decayMinutes = 1)
    {
        $decayMinutes = cast_to_int($decayMinutes);

        $maxAttempts = cast_to_int($maxAttempts);

        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
