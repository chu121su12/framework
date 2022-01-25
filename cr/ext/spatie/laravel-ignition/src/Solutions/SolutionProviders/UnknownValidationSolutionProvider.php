<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use ReflectionClass;
use ReflectionMethod;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Support\StringComparator;
use Throwable;

class UnknownValidationSolutionProvider implements HasSolutionsForThrowable
{
    /*protected */const REGEX = '/Illuminate\\\\Validation\\\\Validator::(?P<method>validate(?!(Attribute|UsingCustomRule))[A-Z][a-zA-Z]+)/m';

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        if (! $throwable instanceof BadMethodCallException) {
            return false;
        }

        return ! is_null($this->getMethodFromExceptionMessage($throwable->getMessage()));
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            BaseSolution::create()
                ->setSolutionTitle('Unknown Validation Rule')
                ->setSolutionDescription($this->getSolutionDescription($throwable)),
        ];
    }

    protected function getSolutionDescription(/*Throwable */$throwable)/*: string*/
    {
        backport_type_throwable($throwable);

        $method = (string)$this->getMethodFromExceptionMessage($throwable->getMessage());

        $possibleMethod = StringComparator::findSimilarText(
            $this->getAvailableMethods()->toArray(),
            $method
        );

        if (empty($possibleMethod)) {
            return '';
        }

        $rule = Str::snake(str_replace('validate', '', $possibleMethod));

        return "Did you mean `{$rule}` ?";
    }

    protected function getMethodFromExceptionMessage(/*string */$message)/*: ?string*/
    {
        $message = cast_to_string($message);

        if (! preg_match(self::REGEX, $message, $matches)) {
            return null;
        }

        return $matches['method'];
    }

    protected function getAvailableMethods()/*: Collection*/
    {
        $class = new ReflectionClass(Validator::class);

        $extensions = Collection::make(app('validator')->make([], [])->extensions)
            ->keys()
            ->map(function (/*string */$extension) {
                $extension = cast_to_string($extension);

                return 'validate'.Str::studly($extension);
            });

        return Collection::make($class->getMethods())
            ->filter(function (ReflectionMethod $method) { return preg_match('/(validate(?!(Attribute|UsingCustomRule))[A-Z][a-zA-Z]+)/', $method->name); })
            ->map(function (ReflectionMethod $method) { return $method->name; })
            ->merge($extensions);
    }
}
