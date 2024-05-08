<?php

namespace Laravel\Octane\Contracts;

use Illuminate\Http\Request;
use Laravel\Octane\RequestContext;

interface ServesStaticFiles
{
    /**
     * Determine if the request can be served as a static file.
     *
     * @return bool
     */
    public function canServeRequestAsStaticFile(Request $request, RequestContext $context)/*: bool*/;

    /**
     * Serve the static file that was requested.
     *
     * @return void
     */
    public function serveStaticFile(Request $request, RequestContext $context)/*: void*/;
}
