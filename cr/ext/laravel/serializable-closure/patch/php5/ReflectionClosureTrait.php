<?php

namespace Laravel\SerializableClosure\Support;

trait ReflectionClosureTrait
{
    #[\ReturnTypeWillChange]
    public function getClosureUsedVariables()
    {
        return $this->getUseVariables();
    }
}
