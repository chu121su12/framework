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
}
