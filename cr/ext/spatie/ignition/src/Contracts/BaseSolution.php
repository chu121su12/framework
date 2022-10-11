<?php

namespace Spatie\Ignition\Contracts;

class BaseSolution implements Solution
{
    protected /*string */$title;

    protected /*string */$description = '';

    /** @var array<string, string> */
    protected /*array */$links = [];

    public static function create(/*string */$title = '')/*: static*/
    {
        $title = backport_type_check('string', $title);

        // It's important to keep the return type as static because
        // the old Facade Ignition contracts extend from this method.

        /** @phpstan-ignore-next-line */
        return new static($title);
    }

    public function __construct(/*string */$title = '')
    {
        $title = backport_type_check('string', $title);

        $this->title = $title;
    }

    public function getSolutionTitle()/*: string*/
    {
        return $this->title;
    }

    public function setSolutionTitle(/*string */$title)/*: self*/
    {
        $title = backport_type_check('string', $title);

        $this->title = $title;

        return $this;
    }

    public function getSolutionDescription()/*: string*/
    {
        return $this->description;
    }

    public function setSolutionDescription(/*string */$description)/*: self*/
    {
        $description = backport_type_check('string', $description);

        $this->description = $description;

        return $this;
    }

    /** @return array<string, string> */
    public function getDocumentationLinks()/*: array*/
    {
        return $this->links;
    }

    /** @param array<string, string> $links */
    public function setDocumentationLinks(array $links)/*: self*/
    {
        $this->links = $links;

        return $this;
    }
}
