<?php

namespace Spatie\LaravelIgnition\Recorders\DumpRecorder;

class Dump
{
    protected string $htmlDump;

    protected ?string $file;

    protected ?int $lineNumber;

    protected float $microtime;

    public function __construct(/*string */$htmlDump, /*?string */$file = null, /*?int */$lineNumber = null, /*?float */$microtime = null = null)
    {
        $htmlDump = cast_to_string($htmlDump);

        $microtime = cast_to_float($microtime, null);

        $lineNumber = cast_to_int($lineNumber, null);

        $file = cast_to_string($file, null);

        $this->htmlDump = $htmlDump;
        $this->file = $file;
        $this->lineNumber = $lineNumber;
        $this->microtime = $microtime ?? microtime(true);
    }

    /** @return array<string, mixed> */
    public function toArray()/*: array*/
    {
        return [
            'html_dump' => $this->htmlDump,
            'file' => $this->file,
            'line_number' => $this->lineNumber,
            'microtime' => $this->microtime,
        ];
    }
}
