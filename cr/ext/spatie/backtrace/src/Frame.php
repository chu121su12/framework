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
        $file = cast_to_string($file);
        $lineNumber = cast_to_int($lineNumber);
        $method = cast_to_string($method, null);
        $class = cast_to_string($class, null);
        $isApplicationFrame = cast_to_bool($isApplicationFrame);

        $this->file = $file;

        $this->lineNumber = $lineNumber;

        $this->arguments = $arguments;

        $this->method = $method;

        $this->class = $class;

        $this->applicationFrame = $isApplicationFrame;
    }

    public function getSnippet(/*int */$lineCount)/*: array*/
    {
        $lineCount = cast_to_int($lineCount);

        return (new CodeSnippet())
            ->surroundingLine($this->lineNumber)
            ->snippetLineCount($lineCount)
            ->get($this->file);
    }

    public function getSnippetProperties(/*int */$lineCount)/*: array*/
    {
        $lineCount = cast_to_int($lineCount);

        $snippet = $this->getSnippet($lineCount);

        return array_map(function (/*int */$lineNumber) use ($snippet) {
            $lineNumber = cast_to_int($lineNumber);

            return [
                'line_number' => $lineNumber,
                'text' => $snippet[$lineNumber],
            ];
        }, array_keys($snippet));
    }
}
