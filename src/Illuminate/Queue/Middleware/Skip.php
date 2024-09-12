<?php

namespace Illuminate\Queue\Middleware;

use Closure;

class Skip
{
    protected $skip;

    public function __construct(/*protected bool */$skip = false)
    {
        $this->skip = backport_type_check('bool', $skip);
    }

    /**
     * Apply the middleware if the given condition is truthy.
     *
     * @param  bool|Closure(): bool  $condition
     */
    public static function when(/*Closure|bool */$condition)/*: self*/
    {
        $condition = backport_type_check('Closure|bool', $condition);

        return new self(value($condition));
    }

    /**
     * Apply the middleware unless the given condition is truthy.
     *
     * @param  bool|Closure(): bool  $condition
     */
    public static function unless(/*Closure|bool */$condition)/*: self*/
    {
        $condition = backport_type_check('Closure|bool', $condition);

        return new self(! value($condition));
    }

    /**
     * Handle the job.
     */
    public function handle(/*mixed */$job, callable $next)/*: mixed*/
    {
        $job = backport_type_check('mixed', $job);

        if ($this->skip) {
            return false;
        }

        return $next($job);
    }
}
