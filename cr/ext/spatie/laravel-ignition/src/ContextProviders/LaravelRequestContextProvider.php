<?php

namespace Spatie\LaravelIgnition\ContextProviders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as LaravelRequest;
use Spatie\FlareClient\Context\RequestContextProvider;
use Symfony\Component\HttpFoundation\Request as SymphonyRequest;
use Throwable;

class LaravelRequestContextProvider extends RequestContextProvider
{
    protected /*null|SymphonyRequest|LaravelRequest */$request;

    public function __construct(LaravelRequest $request)
    {
        $this->request = $request;
    }

    /** @return null|array<string, mixed> */
    public function getUser()/*: array|null*/
    {
        try {
            $request = $this->request;
            /** @var object|null $user */
            /** @phpstan-ignore-next-line */
            $user = isset($request) ? $request->user() : null;

            if (! $user) {
                return null;
            }
        } catch (\Exception $e) {
        } catch (\Error $e) {
        } catch (Throwable $e) {
        }

        if (isset($e)) {
            return null;
        }

        try {
            if (method_exists($user, 'toFlare')) {
                return $user->toFlare();
            }

            if (method_exists($user, 'toArray')) {
                return $user->toArray();
            }
        } catch (\Exception $e) {
        } catch (\Error $e) {
        } catch (Throwable $e) {
        }

        if (isset($e)) {
            return null;
        }

        return null;
    }

    /** @return null|array<string, mixed> */
    public function getRoute()/*: array|null*/
    {
        /**
         * @phpstan-ignore-next-line
         * @var \Illuminate\Routing\Route|null $route
         */
        $route = $this->request->route();

        if (! $route) {
            return null;
        }

        $middlewares = $route->gatherMiddleware();
        return [
            'route' => $route->getName(),
            'routeParameters' => $this->getRouteParameters(),
            'controllerAction' => $route->getActionName(),
            'middleware' => array_values(isset($middlewares) ? $middlewares : []),
        ];
    }

    /** @return array<int, mixed> */
    protected function getRouteParameters()/*: array*/
    {
        try {
            $optionalParameters = optional($this->request->route())->parameters;
            /** @phpstan-ignore-next-line */
            return collect(isset($optionalParameters) ? $optionalParameters : [])
                ->map(function ($parameter) { return $parameter instanceof Model ? $parameter->withoutRelations() : $parameter; })
                ->map(function ($parameter) {
                    return method_exists($parameter, 'toFlare') ? $parameter->toFlare() : $parameter;
                })
                ->toArray();
        } catch (\Exception $e) {
        } catch (\Error $e) {
        } catch (Throwable $e) {
        }

        if (isset($e)) {
            return [];
        }
    }

    /** @return array<int, mixed> */
    public function toArray()/*: array*/
    {
        $properties = parent::toArray();

        if ($route = $this->getRoute()) {
            $properties['route'] = $route;
        }

        if ($user = $this->getUser()) {
            $properties['user'] = $user;
        }

        return $properties;
    }
}
