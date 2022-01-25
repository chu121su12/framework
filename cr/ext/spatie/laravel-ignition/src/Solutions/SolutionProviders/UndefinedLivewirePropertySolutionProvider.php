<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Livewire\Exceptions\PropertyNotFoundException;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Solutions\SuggestLivewirePropertyNameSolution;
use Spatie\LaravelIgnition\Support\LivewireComponentParser;
use Throwable;

class UndefinedLivewirePropertySolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(/*Throwable */$throwable)/*: bool*/
    {
        backport_type_throwable($throwable);

        return $throwable instanceof PropertyNotFoundException;
    }

    public function getSolutions(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        ['variable' => $variable, 'component' => $component] = $this->getMethodAndComponent($throwable);

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

    /**
     * @param \Throwable $throwable
     *
     * @return array<string, string|null>
     */
    protected function getMethodAndComponent(/*Throwable */$throwable)/*: array*/
    {
        backport_type_throwable($throwable);

        preg_match_all('/\[([\d\w\-_\$]*)\]/m', $throwable->getMessage(), $matches, PREG_SET_ORDER, 0);

        return [
            'variable' => isset($matches[0]) && isset($matches[0][1]) ? $matches[0][1] : null,
            'component' => isset($matches[1]) && isset($matches[1][1]) ? $matches[1][1] : null,
        ];
    }
}
