<?php

namespace Doctrine\Patch;

trait PDOStatementComplianceTrait
{
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        return $this->fetchAll_(...func_get_args());
    }

    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        return $this->setFetchMode_(...func_get_args());
    }
}
