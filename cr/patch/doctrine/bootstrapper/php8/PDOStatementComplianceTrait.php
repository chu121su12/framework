<?php

namespace Doctrine\Patch;

trait PDOStatementComplianceTrait
{
    public function fetchAll(int $fetch_style = \PDO::FETCH_BOTH, mixed ...$fetch_args)
    {
        return $this->fetchAll_($fetch_style, ...$fetch_args);
    }

    public function setFetchMode(int $mode, mixed ...$params)
    {
        return $this->setFetchMode_($mode, ...$params);
    }
}
