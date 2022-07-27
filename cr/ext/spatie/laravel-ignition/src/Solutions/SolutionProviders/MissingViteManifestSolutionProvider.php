<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class MissingViteManifestSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        return Str::startsWith($throwable->getMessage(), 'Vite manifest not found');
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            BaseSolution::create('Missing Vite Manifest File')
                ->setSolutionDescription('Did you forget to run `npm install && npm run dev`?'),
        ];
    }
}
