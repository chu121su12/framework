<?php

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Closure;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;

class AddLogs implements FlareMiddleware
{
    protected /*LogRecorder */$logRecorder;

    public function __construct()
    {
        $this->logRecorder = app(LogRecorder::class);
    }

    public function handle(Report $report, Closure $next)
    {
        $report->group('logs', $this->logRecorder->getLogMessages());

        return $next($report);
    }
}
