<?php

namespace Illuminate\Foundation\Defer;

use ArrayAccess;
use Closure;
use Countable;

class DeferredCallbackCollection implements ArrayAccess, Countable
{
    /**
     * All of the deferred callbacks.
     *
     * @var array
     */
    protected /*array */$callbacks = [];

    /**
     * Get the first callback in the collection.
     *
     * @return callable
     */
    public function first()
    {
        return array_values($this->callbacks)[0];
    }

    /**
     * Invoke the deferred callbacks.
     *
     * @return void
     */
    public function invoke()/*: void*/
    {
        $this->invokeWhen(function () { return true; });
    }

    /**
     * Invoke the deferred callbacks if the given truth test evaluates to true.
     *
     * @param  \Closure|null  $when
     * @return void
     */
    public function invokeWhen(/*?*/Closure $when = null)/*: void*/
    {
        if (! isset($when)) {
            $when = function () { return true; };
        }

        $this->forgetDuplicates();

        foreach ($this->callbacks as $index => $callback) {
            if ($when($callback)) {
                rescue($callback);
            }

            unset($this->callbacks[$index]);
        }
    }

    /**
     * Remove any deferred callbacks with the given name.
     *
     * @param  string  $name
     * @return void
     */
    public function forget(/*string */$name)/*: void*/
    {
        $name = backport_type_check('string', $name);

        $this->callbacks = collect($this->callbacks)
            ->reject(function ($callback) use ($name) { return $callback->name === $name; })
            ->values()
            ->all();
    }

    /**
     * Remove any duplicate callbacks.
     *
     * @return $this
     */
    protected function forgetDuplicates()/*: self*/
    {
        $this->callbacks = collect($this->callbacks)
            ->reverse()
            ->unique(function ($c) { return $c->name; })
            ->reverse()
            ->values()
            ->all();

        return $this;
    }

    /**
     * Determine if the collection has a callback with the given key.
     *
     * @param  mixed  $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists(/*mixed */$offset)/*: bool*/
    {
        $offset = backport_type_check('mixed', $offset);

        $this->forgetDuplicates();

        return isset($this->callbacks[$offset]);
    }

    /**
     * Get the callback with the given key.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(/*mixed */$offset)/*: mixed*/
    {
        $offset = backport_type_check('mixed', $offset);

        $this->forgetDuplicates();

        return $this->callbacks[$offset];
    }

    /**
     * Set the callback with the given key.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet(/*mixed */$offset, /*mixed */$value)/*: void*/
    {
        $offset = backport_type_check('mixed', $offset);

        $value = backport_type_check('mixed', $value);

        if (is_null($offset)) {
            $this->callbacks[] = $value;
        } else {
            $this->callbacks[$offset] = $value;
        }
    }

    /**
     * Remove the callback with the given key from the collection.
     *
     * @param  mixed  $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset(/*mixed */$offset)/*: void*/
    {
        $offset = backport_type_check('mixed', $offset);

        $this->forgetDuplicates();

        unset($this->callbacks[$offset]);
    }

    /**
     * Determine how many callbacks are in the collection.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()/*: int*/
    {
        $this->forgetDuplicates();

        return count($this->callbacks);
    }
}
