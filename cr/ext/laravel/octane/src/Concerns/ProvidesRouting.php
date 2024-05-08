<?php

namespace Laravel\Octane\Concerns;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait ProvidesRouting
{
    /**
     * All of the registered Octane routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Register a Octane route.
     *
     * @param  string  $method
     * @param  string  $uri
     * @return void
     */
    public function route(/*string */$method, /*string */$uri, Closure $callback)/*: void*/
    {
        $uri = backport_type_check('string', $uri);

        $method = backport_type_check('string', $method);

        $this->routes[$method.$uri] = $callback;
    }

    /**
     * Determine if a route exists for the given method and URI.
     *
     * @param  string  $method
     * @param  string  $uri
     * @return bool
     */
    public function hasRouteFor(/*string */$method, /*string */$uri)/*: bool*/
    {
        $uri = backport_type_check('string', $uri);

        $method = backport_type_check('string', $method);

        return isset($this->routes[$method.$uri]);
    }

    /**
     * Invoke the route for the given method and URI.
     *
     * @param  string  $method
     * @param  string  $uri
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invokeRoute(Request $request, /*string */$method, /*string */$uri)/*: Response*/
    {
        $uri = backport_type_check('string', $uri);

        $method = backport_type_check('string', $method);

        return call_user_func($this->routes[$method.$uri], $request);
    }

    /**
     * Get the registered Octane routes.
     */
    public function getRoutes()/*: array*/
    {
        return $this->routes;
    }
}
