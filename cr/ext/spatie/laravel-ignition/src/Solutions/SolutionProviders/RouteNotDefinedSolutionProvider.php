<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Facades\Route;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Support\StringComparator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class RouteNotDefinedSolutionProvider implements HasSolutionsForThrowable
{
    /*protected */const REGEX = '/Route \[(.*)\] not defined/m';

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        if (! $throwable instanceof RouteNotFoundException) {
            return false;
        }

        return (bool)preg_match(self::REGEX, $throwable->getMessage(), $matches);
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        preg_match(self::REGEX, $throwable->getMessage(), $matches);

        $missingRoute = isset($matches[1]) ? $matches[1] : null;

        $suggestedRoute = $this->findRelatedRoute($missingRoute);

        if ($suggestedRoute) {
            return [
                BaseSolution::create("{$missingRoute} was not defined.")
                    ->setSolutionDescription("Did you mean `{$suggestedRoute}`?"),
            ];
        }

        return [
            BaseSolution::create("{$missingRoute} was not defined.")
                ->setSolutionDescription('Are you sure that the route is defined'),
        ];
    }

    protected function findRelatedRoute(/*string */$missingRoute)/*: ?string*/
    {
        $missingRoute = cast_to_string($missingRoute);

        Route::getRoutes()->refreshNameLookups();

        return StringComparator::findClosestMatch(array_keys(Route::getRoutes()->getRoutesByName()), $missingRoute);
    }
}
