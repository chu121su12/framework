<?php

namespace Spatie\Backtrace;

class Frame
{
    /** @var string */
    public $file;

    /** @var int */
    public $lineNumber;

    /** @var array|null */
    public $arguments = null;

    /** @var bool */
    public $applicationFrame;

    /** @var string|null */
    public $method;

    /** @var string|null */
    public $class;

    public function __construct(
        /*string */$file,
        /*int */$lineNumber,
        /*?*/array $arguments = null,
        /*string */$method = null,
        /*string */$class = null,
        /*bool */$isApplicationFrame = false
    ) {
        $file = backport_type_check('string', $file);
        $lineNumber = backport_type_check('int', $lineNumber);
        $method = backport_type_check('?string', $method);
        $class = backport_type_check('?string', $class);
        $isApplicationFrame = backport_type_check('bool', $isApplicationFrame);

        $this->file = $file;

        $this->lineNumber = $lineNumber;

        $this->arguments = $arguments;

        $this->method = $method;

        $this->class = $class;

        $this->applicationFrame = $isApplicationFrame;
    }

    public function getSnippet(/*int */$lineCount)/*: array*/
    {
        $lineCount = backport_type_check('int', $lineCount);

        return (new CodeSnippet())
            ->surroundingLine($this->lineNumber)
            ->snippetLineCount($lineCount)
            ->get($this->file);
    }

    public function getSnippetProperties(/*int */$lineCount)/*: array*/
    {
        $lineCount = backport_type_check('int', $lineCount);

        $snippet = $this->getSnippet($lineCount);

        return array_map(function (/*int */$lineNumber) use ($snippet) {
            $lineNumber = backport_type_check('int', $lineNumber);

            return [
                'line_number' => $lineNumber,
                'text' => $snippet[$lineNumber],
            ];
        }, array_keys($snippet));
    }
}
