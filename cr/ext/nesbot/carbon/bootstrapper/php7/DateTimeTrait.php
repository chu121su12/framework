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

    #[\ReturnTypeWillChange]
    public function setTime($hour, $minute, $second = null, $microseconds = null)
    {
        return $this->setTime_(...func_get_args());
    }
}
