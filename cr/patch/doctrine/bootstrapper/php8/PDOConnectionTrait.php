<?php

namespace Doctrine\Patch;

trait PDOConnectionTrait
{
    public function query(string $statement, ?int $fetch_mode = null, mixed ...$fetch_mode_args)
    {
        return $this->query_(...func_get_args());
    }
}
