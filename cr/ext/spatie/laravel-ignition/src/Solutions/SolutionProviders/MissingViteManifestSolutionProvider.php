<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\Ignition\Contracts\Solution;
use Throwable;

class MissingViteManifestSolutionProvider implements HasSolutionsForThrowable
{
    /** @var array<string, string> */
    protected /*array */$links = [
        'Asset bundling with Vite' => 'https://laravel.com/docs/9.x/vite#running-vite',
    ];

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        return Str::startsWith($throwable->getMessage(), 'Vite manifest not found');
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            $this->getSolution(),
        ];
    }

    public function getSolution()/*: Solution*/
    {
        /** @var string */
        $baseCommand = collect([
            'pnpm-lock.yaml' => 'pnpm',
            'yarn.lock' => 'yarn',
        ])->first(function ($_, $lockfile) { return file_exists(base_path($lockfile)); }, 'npm run');

        return app()->environment('local')
            ? $this->getLocalSolution($baseCommand)
            : $this->getProductionSolution($baseCommand);
    }

    protected function getLocalSolution(string $baseCommand)/*: Solution*/
    {
        $baseCommand = backport_type_check('string', $baseCommand);

        return BaseSolution::create('Start the development server')
            ->setSolutionDescription("Run `{$baseCommand} dev` in your terminal and refresh the page.")
            ->setDocumentationLinks($this->links);
    }

    protected function getProductionSolution(string $baseCommand)/*: Solution*/
    {
        $baseCommand = backport_type_check('string', $baseCommand);

        return BaseSolution::create('Build the production assets')
            ->setSolutionDescription("Run `{$baseCommand} build` in your deployment script.")
            ->setDocumentationLinks($this->links);
    }
}
