<?php

namespace Doctrine\Patch;

trait PDOConnectionTrait
{
    public function query()
    {
        return $this->query_(...\func_get_args());
    }
}
