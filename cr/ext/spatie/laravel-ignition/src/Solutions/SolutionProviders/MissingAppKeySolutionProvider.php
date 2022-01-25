<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use RuntimeException;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Solutions\GenerateAppKeySolution;
use Throwable;

class MissingAppKeySolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        if (! $throwable instanceof RuntimeException) {
            return false;
        }

        return $throwable->getMessage() === 'No application encryption key has been specified.';
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [new GenerateAppKeySolution()];
    }
}
