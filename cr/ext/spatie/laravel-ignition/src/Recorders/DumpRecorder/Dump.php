<?php

namespace Spatie\LaravelIgnition\Recorders\DumpRecorder;

class Dump
{
    protected /*string */$htmlDump;

    protected /*?string */$file;

    protected /*?int */$lineNumber;

    protected /*float */$microtime;

    public function __construct(/*string */$htmlDump, /*?string */$file = null, /*?int */$lineNumber = null, /*?float */$microtime = null)
    {
        $htmlDump = backport_type_check('string', $htmlDump);

        $microtime = backport_type_check('?float', $microtime);

        $lineNumber = backport_type_check('?int', $lineNumber);

        $file = backport_type_check('?string', $file);

        $this->htmlDump = $htmlDump;
        $this->file = $file;
        $this->lineNumber = $lineNumber;
        $this->microtime = isset($microtime) ? $microtime : microtime(true);
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
