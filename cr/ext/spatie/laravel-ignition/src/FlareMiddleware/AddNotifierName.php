<?php

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Closure;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddNotifierName implements FlareMiddleware
{
    /*public */const NOTIFIER_NAME = 'Laravel Client';

    public function handle(Report $report, Closure $next)
    {
        $report->notifierName(static::NOTIFIER_NAME);

        return $next($report);
    }
}
