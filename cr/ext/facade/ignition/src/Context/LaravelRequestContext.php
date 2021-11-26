<?php

namespace Facade\Ignition\Context;

use Facade\FlareClient\Context\RequestContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class LaravelRequestContext extends RequestContext
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getUser()/*: array*/
    {
        try {
            $user = $this->request->user();

            if (! $user) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        } catch (\Error $e) {
            return [];
        } catch (\Throwable $e) {
            return [];
        }

        try {
            if (method_exists($user, 'toFlare')) {
                return $user->toFlare();
            }

            if (method_exists($user, 'toArray')) {
                return $user->toArray();
            }
        } catch (\Exception $e) {
            return [];
        } catch (\Error $e) {
            return [];
        } catch (\Throwable $e) {
            return [];
        }

        return [];
    }

    public function getRoute()/*: array*/
    {
        $route = $this->request->route();

        $middlewares = optional($route)->gatherMiddleware();

        return [
            'route' => optional($route)->getName(),
            'routeParameters' => $this->getRouteParameters(),
            'controllerAction' => optional($route)->getActionName(),
            'middleware' => array_values(isset($middlewares) ? $middlewares : []),
        ];
    }

    public function getRequest()/*: array*/
    {
        $properties = parent::getRequest();


        if ($this->request->hasHeader('x-livewire') && $this->request->hasHeader('referer')) {
            $properties['url'] = $this->request->header('referer');
        }

        return $properties;
    }

    protected function getRouteParameters()/*: array*/
    {
        try {
            $parameters = optional($this->request->route())->parameters;

            return collect(isset($parameters) ? $parameters : [])
                ->map(function ($parameter) {
                    return $parameter instanceof Model ? $parameter->withoutRelations() : $parameter;
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        } catch (\Error $e) {
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function toArray()/*: array*/
    {
        $properties = parent::toArray();

        $properties['route'] = $this->getRoute();

        $properties['user'] = $this->getUser();

        return $properties;
    }
}
