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
        $this->variableName = backport_type_check('string', $variableName);
        $this->componentClass = backport_type_check('string', $componentClass);
        $this->suggested = backport_type_check('string', $suggested);
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
