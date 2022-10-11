<?php

namespace Spatie\LaravelIgnition\Solutions;

use Spatie\Ignition\Contracts\Solution;

class SuggestCorrectVariableNameSolution implements Solution
{
    protected /*?string */$variableName;

    protected /*?string */$viewFile;

    protected /*?string */$suggested;

    public function __construct(/*string */$variableName = null, /*string */$viewFile = null, /*string */$suggested = null)
    {
        $suggested = backport_type_check('?string', $suggested);
        $viewFile = backport_type_check('?string', $viewFile);
        $variableName = backport_type_check('?string', $variableName);

        $this->variableName = $variableName;

        $this->viewFile = $viewFile;

        $this->suggested = $suggested;
    }

    public function getSolutionTitle()/*: string*/
    {
        return 'Possible typo $'.$this->variableName;
    }

    public function getDocumentationLinks()/*: array*/
    {
        return [];
    }

    public function getSolutionDescription()/*: string*/
    {
        return "Did you mean `$$this->suggested`?";
    }

    public function isRunnable()/*: bool*/
    {
        return false;
    }
}
