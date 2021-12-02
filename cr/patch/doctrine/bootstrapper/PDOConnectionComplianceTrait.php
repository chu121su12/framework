<?php

namespace Doctrine\Patch;

trait PDOConnectionComplianceTrait
{
    #[\ReturnTypeWillChange]
    public function query()
    {
        return $this->query_(...\func_get_args());
    }
}
