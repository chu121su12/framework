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
    public function __construct($maxAttempts, $decayMinutes = 1)
    {
        $maxAttempts = cast_to_int($maxAttempts);

        $decayMinutes = cast_to_int($decayMinutes);

        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
