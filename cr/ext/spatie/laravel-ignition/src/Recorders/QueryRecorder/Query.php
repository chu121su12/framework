<?php

namespace Spatie\LaravelIgnition\Recorders\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;

class Query
{
    protected /*string */$sql;

    protected /*float */$time;

    protected /*string */$connectionName;

    /** @var array<string, string>|null */
    protected /*?array */$bindings;

    protected /*float */$microtime;

    public static function fromQueryExecutedEvent(QueryExecuted $queryExecuted, /*bool */$reportBindings = false)/*: self*/
    {
        $reportBindings = cast_to_bool($reportBindings);

        return new self(
            $queryExecuted->sql,
            $queryExecuted->time,
            /** @phpstan-ignore-next-line  */
            isset($queryExecuted->connectionName) ? $queryExecuted->connectionName : '',
            $reportBindings ? $queryExecuted->bindings : null
        );
    }

    /**
     * @param string $sql
     * @param float $time
     * @param string $connectionName
     * @param array<string, string>|null $bindings
     * @param float|null $microtime
     */
    protected function __construct(
        /*string */$sql,
        /*float */$time,
        /*string */$connectionName,
        /*?*/array $bindings = null,
        /*?float */$microtime = null
    ) {
        $sql = cast_to_string($sql);
        $time = cast_to_float($time);
        $connectionName = cast_to_string($connectionName);
        $microtime = cast_to_float($microtime, null);

        $this->sql = $sql;
        $this->time = $time;
        $this->connectionName = $connectionName;
        $this->bindings = $bindings;
        $this->microtime = isset($microtime) ? $microtime : microtime(true);
    }

    /**
     * @return array<string, mixed>
     */
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
