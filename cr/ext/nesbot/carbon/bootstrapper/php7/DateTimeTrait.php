<?php

namespace Carbon\Patch;

trait DateTimeTrait
{
    #[\ReturnTypeWillChange]
    public function add($interval)
    {
        return $this->add_(...func_get_args());
    }

    #[\ReturnTypeWillChange]
    public function sub($interval)
    {
        return $this->sub_(...func_get_args());
    }
}
