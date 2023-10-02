<?php

namespace Carbon\Patch;

trait DateTimeTrait
{
    public function add(...$arguments)
    {
        return $this->add_(...$arguments);
    }

    public function sub(...$arguments)
    {
        return $this->sub_(...$arguments);
    }

    public function setTime($hour, $minute, $second = null)
    {
        return $this->setTime_(...func_get_args());
    }
}
