<?php

namespace Laravel\Prompts;

use Closure;

class FormStep
{
    protected /*readonly *//*Closure */$condition;

    protected /*readonly *//*Closure */$step;
    public /*readonly *//*?string */$name;
    protected /*readonly *//*bool */$ignoreWhenReverting;

    public function __construct(
        /*protected *//*readonly */Closure $step,
        /*bool|Closure */$condition,
        /*public *//*readonly *//*?string */$name,
        /*protected *//*readonly *//*bool */$ignoreWhenReverting
    ) {
        $this->step = $step;
        $this->name = backport_type_check('?string', $name);
        $this->ignoreWhenReverting = backport_type_check('bool', $ignoreWhenReverting);

        $condition = backport_type_check('bool|Closure', $condition);

        $this->condition = is_bool($condition)
            ? function () use ($condition) { return $condition; }
            : $condition;
    }

    /**
     * Execute this step.
     *
     * @param  array<mixed>  $responses
     */
    public function run(array $responses, /*mixed */$previousResponse)/*: mixed*/
    {
        $previousResponse = backport_type_check('mixed', $previousResponse);

        if (! $this->shouldRun($responses)) {
            return null;
        }

        return \call_user_func($this->step, $responses, $previousResponse);
    }

    /**
     * Whether the step should run based on the given condition.
     *
     * @param  array<mixed>  $responses
     */
    protected function shouldRun(array $responses)/*: bool*/
    {
        return \call_user_func($this->condition, $responses);
    }

    /**
     * Whether this step should be skipped over when a subsequent step is reverted.
     *
     * @param  array<mixed>  $responses
     */
    public function shouldIgnoreWhenReverting(array $responses)/*: bool*/
    {
        if (! $this->shouldRun($responses)) {
            return true;
        }

        return $this->ignoreWhenReverting;
    }
}
