<?php

namespace Spatie\LaravelIgnition\Solutions;

use Spatie\Ignition\Contracts\Solution;

class SuggestLivewirePropertyNameSolution implements Solution
{
    protected /*string */$variableName;
    protected /*string */$componentClass;
    protected /*string */$suggested;

    public function __construct(
        /*protected *//*string */$variableName,
        /*protected *//*string */$componentClass,
        /*protected *//*string */$suggested
    ) {
        $this->variableName = cast_to_string($variableName);
        $this->componentClass = cast_to_string($componentClass);
        $this->suggested = cast_to_string($suggested);
    }

    public function getSolutionTitle()/*: string*/
    {
        return "Possible typo {$this->variableName}";
    }

    public function getDocumentationLinks()/*: array*/
    {
        return [];
    }

    public function getSolutionDescription()/*: string*/
    {
        return "Did you mean `$this->suggested`?";
    }

    public function isRunnable()/*: bool*/
    {
        return false;
    }
}
