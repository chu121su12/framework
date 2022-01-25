<?php

namespace Spatie\Backtrace;

use RuntimeException;

class CodeSnippet
{
    /** @var int */
    protected $surroundingLine = 1;

    /** @var int */
    protected $snippetLineCount = 9;

    public function surroundingLine(/*int */$surroundingLine)/*: self*/
    {
        $surroundingLine = cast_to_int($surroundingLine);

        $this->surroundingLine = $surroundingLine;

        return $this;
    }

    public function snippetLineCount(/*int */$snippetLineCount)/*: self*/
    {
        $snippetLineCount = cast_to_int($snippetLineCount);

        $this->snippetLineCount = $snippetLineCount;

        return $this;
    }

    public function get(/*string */$fileName)/*: array*/
    {
        $fileName = cast_to_string($fileName);

        if (! file_exists($fileName)) {
            return [];
        }

        try {
            $file = new File($fileName);

            list($startLineNumber, $endLineNumber) = $this->getBounds($file->numberOfLines());

            $code = [];

            $line = $file->getLine($startLineNumber);

            $currentLineNumber = $startLineNumber;

            while ($currentLineNumber <= $endLineNumber) {
                $code[$currentLineNumber] = rtrim(substr($line, 0, 250));

                $line = $file->getNextLine();
                $currentLineNumber++;
            }

            return $code;
        } catch (RuntimeException $exception) {
            return [];
        }
    }

    protected function getBounds(/*int */$totalNumberOfLineInFile)/*: array*/
    {
        $totalNumberOfLineInFile = cast_to_int($totalNumberOfLineInFile);

        $startLine = max($this->surroundingLine - floor($this->snippetLineCount / 2), 1);

        $endLine = $startLine + ($this->snippetLineCount - 1);

        if ($endLine > $totalNumberOfLineInFile) {
            $endLine = $totalNumberOfLineInFile;
            $startLine = max($endLine - ($this->snippetLineCount - 1), 1);
        }

        return [$startLine, $endLine];
    }
}
