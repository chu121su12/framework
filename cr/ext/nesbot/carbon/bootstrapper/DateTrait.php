<?php

namespace Carbon\Patch;

trait DateTrait
{
    public function __serialize()/*: array*/
    {
        return $this->__serialize_(...func_get_args());
    }

    public function __unserialize(array $data)/*: void*/
    {
        $this->__unserialize_(...func_get_args());
    }
}
