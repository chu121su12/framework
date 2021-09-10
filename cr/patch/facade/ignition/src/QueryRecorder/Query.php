<?php

namespace Facade\Ignition\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;

class Query
{
    /** @var string */
    protected $sql;

    /** @var float */
    protected $time;

    /** @var string */
    protected $connectionName;

    /** @var null|array */
    protected $bindings;

    /** @var float */
    protected $microtime;

    public static function fromQueryExecutedEvent(QueryExecuted $queryExecuted, /*bool */$reportBindings = false)
    {
        $reportBindings = cast_to_bool($reportBindings);

        return new static(
            $queryExecuted->sql,
            $queryExecuted->time,
            isset($queryExecuted->connectionName) ? $queryExecuted->connectionName : '',
            $reportBindings ? $queryExecuted->bindings : null
        );
    }

    protected function __construct(
        /*string */$sql,
        /*float */$time,
        /*string */$connectionName,
        /*?array */$bindings = null,
        /*?float */$microtime = null
    ) {
        $sql = cast_to_string($sql);
        $time = cast_to_float($time);
        $connectionName = cast_to_string($connectionName);
        $bindings = cast_to_array($bindings, null);
        $microtime = cast_to_float($microtime, null);

        $this->sql = $sql;
        $this->time = $time;
        $this->connectionName = $connectionName;
        $this->bindings = $bindings;
        $this->microtime = isset($microtime) ? $microtime : microtime(true);
    }

    public function toArray()/*: array*/
    {
        return [
            'sql' => $this->sql,
            'time' => $this->time,
            'connection_name' => $this->connectionName,
            'bindings' => $this->bindings,
            'microtime' => $this->microtime,
        ];
    }
}
