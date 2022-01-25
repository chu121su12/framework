<?php

namespace Spatie\LaravelIgnition\Http\Middleware;

use Closure;

class RunnableSolutionsEnabled
{
    public function handle($request, Closure $next)
    {
        if (! $this->ignitionEnabled()) {
            abort(404);
        }

        return $next($request);
    }

    protected function ignitionEnabled()/*: bool*/
    {
        $runnableSolutions = config('ignition.enable_runnable_solutions');
        return isset($runnableSolutions) ? $runnableSolutions : config('app.debug');
    }
}
