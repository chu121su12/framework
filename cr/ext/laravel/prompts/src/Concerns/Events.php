<?php

namespace Laravel\Prompts\Concerns;

use Closure;

trait Events
{
    /**
     * The registered event listeners.
     *
     * @var array<string, array<int, Closure>>
     */
    protected /*array */$listeners = [];

    /**
     * Register an event listener.
     */
    public function on(/*string */$event, Closure $callback)/*: void*/
    {
        $event = backport_type_check('string', $event);

        $this->listeners[$event][] = $callback;
    }

    /**
     * Emit an event.
     */
    public function emit(/*string */$event, /*mixed */...$data)/*: void*/
    {
        $event = backport_type_check('string', $event);

        $data = backport_array_type_check('mixed', $data);

        foreach (isset($this->listeners[$event]) ? $this->listeners[$event] : [] as $listener) {
            $listener(...$data);
        }
    }
}
