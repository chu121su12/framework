<?php

namespace Facade\Ignition\DumpRecorder;

class Dump
{
    /** @var string */
    protected $htmlDump;

    /** @var ?string */
    protected $file;

    /** @var ?int */
    protected $lineNumber;

    /** @var float */
    protected $microtime;

    public function __construct(/*string */$htmlDump, /*?string */$file, /*?int */$lineNumber, /*?float */$microtime = null)
    {
        $htmlDump = cast_to_string($htmlDump);
        $file = cast_to_string($file, null);
        $lineNumber = cast_to_int($lineNumber, null);
        $microtime = cast_to_float($microtime, null);

        $this->htmlDump = $htmlDump;
        $this->file = $file;
        $this->lineNumber = $lineNumber;
        $this->microtime = isset($microtime) ? $microtime : microtime(true);
    }

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
