<?php

namespace Illuminate\Support;

use Closure;

class Benchmark
{
    /**
     * Measure a callable or array of callables over the given number of iterations.
     *
     * @param  \Closure|array  $benchmarkables
     * @param  int  $iterations
     * @return array|float
     */
    public static function measure(/*Closure|array */$benchmarkables, /*int */$iterations = 1)/*: array|float*/
    {
        $benchmarkables = backport_type_check('\Closure|array', $benchmarkables);

        $iterations = backport_type_check('int', $iterations);

        return collect(Arr::wrap($benchmarkables))->map(function ($callback) use ($iterations) {
            return collect(range(1, $iterations))->map(function () use ($callback) {
                gc_collect_cycles();

                $start = hrtime(true);

                $callback();

                return (hrtime(true) - $start) / 1000000;
            })->average();
        })->when(
            $benchmarkables instanceof Closure,
            function ($c) { return $c->first(); },
            function ($c) { return $c->all(); }
        );
    }

    /**
     * Measure a callable or array of callables over the given number of iterations, then dump and die.
     *
     * @param  \Closure|array  $benchmarkables
     * @param  int  $iterations
     * @return never
     */
    public static function dd(/*Closure|array */$benchmarkables, /*int */$iterations = 1)/*: void*/
    {
        $benchmarkables = backport_type_check('\Closure|array', $benchmarkables);

        $iterations = backport_type_check('int', $iterations);

        $result = collect(static::measure(Arr::wrap($benchmarkables), $iterations))
            ->map(function ($average) { return number_format($average, 3).'ms'; })
            ->when($benchmarkables instanceof Closure, function ($c) { return $c->first(); }, function ($c) { return $c->all(); });

        dd($result);
    }
}
