<?php

namespace Illuminate\Support\Traits;

use Closure;
use Illuminate\Support\HigherOrderWhenProxy;

trait Conditionable
{
    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param  (\Closure($this): TWhenParameter)|TWhenParameter  $value
     * @param  (callable($this, TWhenParameter): TWhenReturnType)|null  $callback
     * @param  (callable($this, TWhenParameter): TWhenReturnType)|null  $default
     * @return $this|TWhenReturnType
     */
    public function when($value, callable $callback = null, callable $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (func_num_args() === 1) {
            return new HigherOrderWhenProxy($this, $value);
        }

        if ($value) {
            $result = $callback($this, $value);
            return isset($result) ? $result : $this;
        } elseif ($default) {
            $result = $default($this, $value);
            return isset($result) ? $result : $this;
        }

        return $this;
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     *
     * @template TUnlessParameter
     * @template TUnlessReturnType
     *
     * @param  (\Closure($this): TUnlessParameter)|TUnlessParameter  $value
     * @param  (callable($this, TUnlessParameter): TUnlessReturnType)|null  $callback
     * @param  (callable($this, TUnlessParameter): TUnlessReturnType)|null  $default
     * @return $this|TUnlessReturnType
     */
    public function unless($value, callable $callback = null, callable $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (func_num_args() === 1) {
            return new HigherOrderWhenProxy($this, ! $value);
        }

        if (! $value) {
            $result = $callback($this, $value);
            return isset($result) ? $result : $this;
        } elseif ($default) {
            $result = $default($this, $value);
            return isset($result) ? $result : $this;
        }

        return $this;
    }
}
