<?php

namespace Doctrine\Patch;

trait PDOConnectionComplianceTrait
{
    public function query()
    {
        return $this->query_(...\func_get_args());
    }
}
