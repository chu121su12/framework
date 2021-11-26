<?php

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\MissingPackageSolution;
use Facade\Ignition\Support\Packagist\Package;
use Facade\Ignition\Support\Packagist\Packagist;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Illuminate\Support\Str;
use Throwable;

class MissingPackageSolutionProvider implements HasSolutionsForThrowable
{
    /** @var \Facade\Ignition\Support\Packagist\Package|null */
    protected $package;

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        $pattern = '/Class \'([^\s]+)\' not found/m';

        if (! preg_match($pattern, $throwable->getMessage(), $matches)) {
            return false;
        }

        $class = $matches[1];

        if (Str::startsWith($class, app()->getNamespace())) {
            return false;
        }

        $this->package = $this->findPackageFromClassName($class);

        return ! is_null($this->package);
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        return [new MissingPackageSolution($this->package)];
    }

    protected function findPackageFromClassName(/*string */$missingClassName)/*: ?Package*/
    {
        $missingClassName = cast_to_string($missingClassName);

        if (! $package = $this->findComposerPackageForClassName($missingClassName)) {
            return null;
        }

        return $package->hasNamespaceThatContainsClassName($missingClassName)
            ? $package
            : null;
    }

    protected function findComposerPackageForClassName(/*string */$className)/*: ?Package*/
    {
        $className = cast_to_string($className);

        $packages = Packagist::findPackagesForClassName($className);

        return isset($packages[0]) ? $packages[0] : null;
    }
}
