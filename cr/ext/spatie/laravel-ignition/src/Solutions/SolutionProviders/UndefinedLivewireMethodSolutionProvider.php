<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Livewire\Exceptions\MethodNotFoundException;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Solutions\SuggestLivewireMethodNameSolution;
use Spatie\LaravelIgnition\Support\LivewireComponentParser;
use Throwable;

class UndefinedLivewireMethodSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        return $throwable instanceof MethodNotFoundException;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        $methodAndComponentlist = $this->getMethodAndComponent($throwable);
        $methodName = $methodAndComponentlist['methodName'];
        $component = $methodAndComponentlist['component'];

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

    /** @return array<string, string|null> */
    protected function getMethodAndComponent(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        preg_match_all('/\[([\d\w\-_]*)\]/m', $throwable->getMessage(), $matches, PREG_SET_ORDER);

        return [
            'methodName' => isset($matches[0]) && isset($matches[0][1]) ? $matches[0][1] : null,
            'component' => isset($matches[1]) && isset($matches[1][1]) ? $matches[1][1] : null,
        ];
    }
}
