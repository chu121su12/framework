<?php

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\SuggestLivewirePropertyNameSolution;
use Facade\Ignition\Support\LivewireComponentParser;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Throwable;

class UndefinedLivewirePropertySolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        return $throwable instanceof PropertyNotFoundException || $throwable instanceof PublicPropertyNotFoundException;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        $methodAndComponent = $this->getMethodAndComponent($throwable);
        $variable = $methodAndComponent['component'];
        $component = $methodAndComponent['variable'];

        if ($variable === null || $component === null) {
            return [];
        }

        $parsed = LivewireComponentParser::create($component);

        return $parsed->getPropertyNamesLike($variable)
            ->map(function (/*string */$suggested) use ($parsed, $variable) {
                $suggested = cast_to_string($suggested);

                return new SuggestLivewirePropertyNameSolution(
                    $variable,
                    $parsed->getComponentClass(),
                    '$'.$suggested
                );
            })
            ->toArray();
    }

    protected function getMethodAndComponent(/*Throwable */$throwable)/*: array*/
    {
        preg_match_all('/\[([\d\w\-_\$]*)\]/m', $throwable->getMessage(), $matches, PREG_SET_ORDER, 0);

        return [
            'variable' => isset($matches[0]) && isset($matches[0][1]) ? $matches[0][1] : null,
            'component' => isset($matches[1]) && isset($matches[1][1]) ? $matches[1][1] : null,
        ];
    }
}
