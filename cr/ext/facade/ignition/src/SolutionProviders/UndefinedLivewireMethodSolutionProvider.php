<?php

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\SuggestLivewireMethodNameSolution;
use Facade\Ignition\Support\LivewireComponentParser;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Livewire\Exceptions\MethodNotFoundException;
use Throwable;

class UndefinedLivewireMethodSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        return $throwable instanceof MethodNotFoundException;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        $methodAndComponent = $this->getMethodAndComponent($throwable);
        $methodName = $methodAndComponent['methodName'];
        $component = $methodAndComponent['component'];

        if ($methodName === null || $component === null) {
            return [];
        }

        $parsed = LivewireComponentParser::create($component);

        return $parsed->getMethodNamesLike($methodName)
            ->map(function (/*string */$suggested) use ($parsed, $methodName) {
                $suggested = cast_to_string($suggested);

                return new SuggestLivewireMethodNameSolution(
                    $methodName,
                    $parsed->getComponentClass(),
                    $suggested
                );
            })
            ->toArray();
    }

    protected function getMethodAndComponent(/*Throwable */$throwable)/*: array*/
    {
        preg_match_all('/\[([\d\w\-_]*)\]/m', $throwable->getMessage(), $matches, PREG_SET_ORDER);

        return [
            'methodName' => isset($matches[0]) && isset($matches[0][1]) ? $matches[0][1] : null,
            'component' => isset($matches[1]) && isset($matches[1][1]) ? $matches[1][1] : null,
        ];
    }
}
