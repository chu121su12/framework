<?php

namespace Spatie\Ignition\Solutions;

use Spatie\Ignition\Contracts\Solution;

class SuggestCorrectVariableNameSolution implements Solution
{
    protected ?string $variableName;

    protected ?string $viewFile;

    protected ?string $suggested;

    public function __construct(/*string */$variableName = null, /*string */$viewFile = null, /*string */$suggested = null)
    {
        $suggested = cast_to_string($suggested, null);
        $viewFile = cast_to_string($viewFile, null);
        $variableName = cast_to_string($variableName, null);

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
