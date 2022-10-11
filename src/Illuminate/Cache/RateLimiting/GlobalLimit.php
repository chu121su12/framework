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
        $decayMinutes = backport_type_check('int', $decayMinutes);

        $maxAttempts = backport_type_check('int', $maxAttempts);

        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
