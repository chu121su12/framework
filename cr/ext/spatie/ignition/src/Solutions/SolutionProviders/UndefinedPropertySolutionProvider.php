<?php

namespace Spatie\Ignition\Solutions\SolutionProviders;

use ErrorException;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class UndefinedPropertySolutionProvider implements HasSolutionsForThrowable
{
    /*protected */const REGEX = '/([a-zA-Z\\\\]+)::\$([a-zA-Z]+)/m';
    /*protected */const MINIMUM_SIMILARITY = 80;

    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        if (! $throwable instanceof ErrorException) {
            return false;
        }

        if (is_null($this->getClassAndPropertyFromExceptionMessage($throwable->getMessage()))) {
            return false;
        }

        if (! $this->similarPropertyExists($throwable)) {
            return false;
        }

        return true;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        return [
            BaseSolution::create('Unknown Property')
            ->setSolutionDescription($this->getSolutionDescription($throwable)),
        ];
    }

    public function getSolutionDescription(/*Throwable */$throwable)/*: string*/
    {
        backport_type_throwable($throwable);

        if (! $this->canSolve($throwable) || ! $this->similarPropertyExists($throwable)) {
            return '';
        }

        extract(
            /** @phpstan-ignore-next-line */
            $this->getClassAndPropertyFromExceptionMessage($throwable->getMessage()),
            EXTR_OVERWRITE
        );

        $possibleProperty = $this->findPossibleProperty(isset($class) ? $class : '', isset($property) ? $property : '');

        $class = isset($class) ? $class : '';

        return "Did you mean {$class}::\${$possibleProperty->name} ?";
    }

    protected function similarPropertyExists(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        /** @phpstan-ignore-next-line  */
        extract($this->getClassAndPropertyFromExceptionMessage($throwable->getMessage()), EXTR_OVERWRITE);

        $possibleProperty = $this->findPossibleProperty(isset($class) ? $class : '', isset($property) ? $property : '');

        return $possibleProperty !== null;
    }

    /**
     * @param string $message
     *
     * @return null|array<string, string>
     */
    protected function getClassAndPropertyFromExceptionMessage(/*string */$message)/*: ?array*/
    {
        $message = backport_type_check('string', $message);

        if (! preg_match(self::REGEX, $message, $matches)) {
            return null;
        }

        return [
            'class' => $matches[1],
            'property' => $matches[2],
        ];
    }

    /**
     * @param class-string $class
     * @param string $invalidPropertyName
     *
     * @return mixed
     */
    protected function findPossibleProperty(/*string */$class, /*string */$invalidPropertyName)/*: mixed*/
    {
        $invalidPropertyName = backport_type_check('string', $invalidPropertyName);

        $class = backport_type_check('string', $class);

        return $this->getAvailableProperties($class)
            ->sortByDesc(function (ReflectionProperty $property) use ($invalidPropertyName) {
                similar_text($invalidPropertyName, $property->name, $percentage);

                return $percentage;
            })
            ->filter(function (ReflectionProperty $property) use ($invalidPropertyName) {
                similar_text($invalidPropertyName, $property->name, $percentage);

                return $percentage >= self::MINIMUM_SIMILARITY;
            })->first();
    }

    /**
     * @param class-string $class
     *
     * @return Collection<int, ReflectionProperty>
     */
    protected function getAvailableProperties(/*string */$class)/*: Collection*/
    {
        $class = backport_type_check('string', $class);

        $class = new ReflectionClass($class);

        return Collection::make($class->getProperties());
    }
}
