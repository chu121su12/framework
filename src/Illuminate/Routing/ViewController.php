<?php

namespace Illuminate\Routing;

use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class ViewController extends Controller
{
    /**
     * The response factory implementation.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $response;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Routing\ResponseFactory  $response
     * @return void
     */
    public function __construct(ResponseFactoryContract $response)
    {
        $this->response = $response;
    }

    /**
     * Invoke the controller method.
     *
     * @param  array  $args
     * @return \Illuminate\Http\Response
     */
    public function __invoke(...$args)
    {
        if (array_is_list($args)) {
            $args = [
                'view' => isset($args[0]) ? $args[0] : null,
                'data' => isset($args[1]) ? $args[1] : null,
                'status' => isset($args[2]) ? $args[2] : null,
                'headers' => isset($args[3]) ? $args[3] : null,
            ];
        }

        $routeParameters = array_filter($args, function ($key) {
            return ! in_array($key, ['view', 'data', 'status', 'headers']);
        }, ARRAY_FILTER_USE_KEY);

        $args['data'] = array_merge($args['data'], $routeParameters);

        return $this->response->view(
            $args['view'],
            $args['data'],
            $args['status'],
            $args['headers']
        );
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        if ($method === '__invoke' && \version_compare(\PHP_VERSION, '8', '<')) {
            $routeParameters = array_filter($parameters, function ($key) {
                return ! in_array($key, ['view', 'data', 'status', 'headers']);
            }, ARRAY_FILTER_USE_KEY);

            $parameters = [
                isset($parameters['view']) ? $parameters['view'] : null,
                array_merge(isset($parameters['data']) ? $parameters['data'] : [], $routeParameters),
                isset($parameters['status']) ? $parameters['status'] : null,
                isset($parameters['headers']) ? $parameters['headers'] : null,
            ];
        }

        return $this->{$method}(...$parameters);
    }
}
