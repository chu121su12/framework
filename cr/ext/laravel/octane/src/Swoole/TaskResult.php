<?php

namespace Laravel\Octane\Swoole;

class TaskResult
{
    public $result;

    public function __construct(/*public mixed */$result)
    {
        $this->result = $result;
    }
}
