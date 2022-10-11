<?php

namespace Spatie\LaravelIgnition\Recorders\QueryRecorder;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;

class QueryRecorder
{
    /** @var \Spatie\LaravelIgnition\Recorders\QueryRecorder\Query[] */
    protected /*array */$queries = [];

    protected /*Application */$app;

    protected /*bool */$reportBindings = true;

    protected /*?int */$maxQueries;

    public function __construct(
        Application $app,
        /*bool */$reportBindings = true,
        /*?int */$maxQueries = null
    ) {
        $reportBindings = backport_type_check('bool', $reportBindings);
        $maxQueries = backport_type_check('?int', $maxQueries);

        $this->app = $app;
        $this->reportBindings = $reportBindings;
        $this->maxQueries = $maxQueries;
    }

    public function start()/*: self*/
    {
        /** @phpstan-ignore-next-line  */
        $this->app['events']->listen(QueryExecuted::class, [$this, 'record']);

        return $this;
    }

    public function record(QueryExecuted $queryExecuted)/*: void*/
    {
        $this->queries[] = Query::fromQueryExecutedEvent($queryExecuted, $this->reportBindings);

        if (is_int($this->maxQueries)) {
            $this->queries = array_slice($this->queries, -$this->maxQueries);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getQueries()/*: array*/
    {
        $queries = [];

        foreach ($this->queries as $query) {
            $queries[] = $query->toArray();
        }

        return $queries;
    }

    public function reset()/*: void*/
    {
        $this->queries = [];
    }

    public function getReportBindings()/*: bool*/
    {
        return $this->reportBindings;
    }

    public function setReportBindings(/*bool */$reportBindings)/*: self*/
    {
        $reportBindings = backport_type_check('bool', $reportBindings);

        $this->reportBindings = $reportBindings;

        return $this;
    }

    public function getMaxQueries()/*: ?int*/
    {
        return $this->maxQueries;
    }

    public function setMaxQueries(/*?int */$maxQueries = null)/*: self*/
    {
        $maxQueries = backport_type_check('?int', $maxQueries);

        $this->maxQueries = $maxQueries;

        return $this;
    }
}
