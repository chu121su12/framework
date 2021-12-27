<?php

namespace Laravel\Jetstream;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InertiaManager
{
    /**
     * The registered rendering callbacks.
     *
     * @var array
     */
    protected $renderingCallbacks = [];

    /**
     * Render the given Inertia page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $page
     * @param  array  $data
     * @return \Inertia\Response
     */
    public function render(Request $request, /*string */$page, array $data = [])
    {
        $page = cast_to_string($page);

        if (isset($this->renderingCallbacks[$page])) {
            foreach ($this->renderingCallbacks[$page] as $callback) {
                $data = $callback($request, $data);
            }
        }

        return Inertia::render($page, $data);
    }

    /**
     * Register a rendering callback.
     *
     * @param  string  $page
     * @param  callable  $callback
     * @return $this
     */
    public function whenRendering(/*string */$page, callable $callback)
    {
        $page = cast_to_string($page);

        $this->renderingCallbacks[$page][] = $callback;

        return $this;
    }
}
