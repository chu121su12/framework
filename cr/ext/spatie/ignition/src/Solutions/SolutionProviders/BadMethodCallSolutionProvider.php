<?php

namespace Spatie\Ignition\Solutions\SolutionProviders;

use BadMethodCallException;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class BadMethodCallSolutionProvider implements HasSolutionsForThrowable
{
    /*protected */const REGEX = '/([a-zA-Z\\\\]+)::([a-zA-Z]+)/m';

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        if (! $throwable instanceof BadMethodCallException) {
            return false;
        }

        if (is_null($this->getClassAndMethodFromExceptionMessage($throwable->getMessage()))) {
            return false;
        }

        return true;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            BaseSolution::create('Bad Method Call')
            ->setSolutionDescription($this->getSolutionDescription($throwable)),
        ];
    }

    public function getSolutionDescription(/*Throwable */$throwable)/*: string*/
    {
        backport_type_throwable($throwable);

        if (! $this->canSolve($throwable)) {
            return '';
        }

        /** @phpstan-ignore-next-line  */
        extract($this->getClassAndMethodFromExceptionMessage($throwable->getMessage()), EXTR_OVERWRITE);

        $possibleMethod = $this->findPossibleMethod(isset($class) ? $class : '', isset($method) ? $method : '');

        if (! isset($class)) {
            $class = 'UnknownClass';
        }

        $possibleMethodName = isset($possibleMethod) ? $possibleMethod->name : null;

        return "Did you mean {$class}::{$possibleMethodName}() ?";
    }

    /**
     * @param string $message
     *
     * @return null|array<string, mixed>
     */
    protected function getClassAndMethodFromExceptionMessage(/*string */$message)/*: ?array*/
    {
        $message = cast_to_string($message);

        if (! preg_match(self::REGEX, $message, $matches)) {
            return null;
        }

        return [
            'class' => $matches[1],
            'method' => $matches[2],
        ];
    }

    /**
     * @param class-string $class
     * @param string $invalidMethodName
     *
     * @return \ReflectionMethod|null
     */
    protected function findPossibleMethod(/*string */$class, /*string */$invalidMethodName)/*: ?ReflectionMethod*/
    {
        $invalidMethodName = cast_to_string($invalidMethodName);

        $class = cast_to_string($class);

        return $this->getAvailableMethods($class)
            ->sortByDesc(function (ReflectionMethod $method) use ($invalidMethodName) {
                similar_text($invalidMethodName, $method->name, $percentage);

                return $percentage;
            })->first();
    }

    /**
     * @param class-string $class
     *
     * @return \Illuminate\Support\Collection<int, ReflectionMethod>
     */
    protected function getAvailableMethods(/*string */$class)/*: Collection*/
    {
        $class = cast_to_string($class);

        $class = new ReflectionClass($class);

        return Collection::make($class->getMethods());
    }
}
