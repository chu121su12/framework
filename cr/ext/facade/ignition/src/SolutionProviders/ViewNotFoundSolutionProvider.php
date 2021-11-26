<?php

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Exceptions\ViewException;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class ViewNotFoundSolutionProvider implements HasSolutionsForThrowable
{
    /*protected */const REGEX = '/View \[(.*)\] not found/m';

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        if (! $throwable instanceof InvalidArgumentException && ! $throwable instanceof ViewException) {
            return false;
        }

        return (bool)preg_match(self::REGEX, $throwable->getMessage(), $matches);
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        preg_match(self::REGEX, $throwable->getMessage(), $matches);

        $missingView = isset($matches[1]) ? $matches[1] : null;

        $suggestedView = $this->findRelatedView($missingView);

        if ($suggestedView) {
            return [
                BaseSolution::create("{$missingView} was not found.")
                    ->setSolutionDescription("Did you mean `{$suggestedView}`?"),
            ];
        }

        return [
            BaseSolution::create("{$missingView} was not found.")
                ->setSolutionDescription('Are you sure the view exists and is a `.blade.php` file?'),
        ];
    }

    protected function findRelatedView(/*string */$missingView)/*: ?string*/
    {
        $missingView = cast_to_string($missingView);

        $views = $this->getAllViews();

        return StringComparator::findClosestMatch($views, $missingView);
    }

    protected function getAllViews()/*: array*/
    {
        /** @var \Illuminate\View\FileViewFinder $fileViewFinder */
        $fileViewFinder = View::getFinder();

        $extensions = $fileViewFinder->getExtensions();

        $viewsForHints = collect($fileViewFinder->getHints())
            ->flatMap(function ($paths, /*string */$namespace) use ($extensions) {
                $namespace = cast_to_string($namespace);
                $paths = Arr::wrap($paths);

                return collect($paths)
                    ->flatMap(function (/*string */$path) use ($extensions) {
                        $path = cast_to_string($path);
                        return $this->getViewsInPath($path, $extensions);
                    })
                    ->map(function (/*string */$view) use ($namespace) {
                        $view = cast_to_string($view);
                        return "{$namespace}::{$view}";
                    })
                    ->toArray();
            });

        $viewsForViewPaths = collect($fileViewFinder->getPaths())
            ->flatMap(function (/*string */$path) use ($extensions) {
                $path = cast_to_string($path);
                return $this->getViewsInPath($path, $extensions);
            });

        return $viewsForHints->merge($viewsForViewPaths)->toArray();
    }

    protected function getViewsInPath(/*string */$path, array $extensions)/*: array*/
    {
        $path = cast_to_string($path);

        $filePatterns = array_map(function (/*string */$extension) {
            $extension = cast_to_string($extension);
            return "*.{$extension}";
        }, $extensions);

        $extensionsWithDots = array_map(function (/*string */$extension) {
            $extension = cast_to_string($extension);
            return ".{$extension}";
        }, $extensions);

        $files = (new Finder())
            ->in($path)
            ->files();

        foreach ($filePatterns as $filePattern) {
            $files->name($filePattern);
        }

        $views = [];

        foreach ($files as $file) {
            if ($file instanceof SplFileInfo) {
                $view = $file->getRelativePathname();
                $view = str_replace($extensionsWithDots, '', $view);
                $view = str_replace('/', '.', $view);
                $views[] = $view;
            }
        }

        return $views;
    }
}
