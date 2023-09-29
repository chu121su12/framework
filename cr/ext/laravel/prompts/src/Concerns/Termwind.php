<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Output\BufferedConsoleOutput;

use function Termwind\render;
use function Termwind\renderUsing;

trait Termwind
{
    protected function termwind(/*string */$html)
    {
        $html = backport_type_check('string', $html);

        renderUsing($output = new BufferedConsoleOutput());

        render($html);

        return $this->restoreEscapeSequences($output->fetch());
    }

    protected function restoreEscapeSequences(/*string */$string)
    {
        $string = backport_type_check('string', $string);

        return preg_replace('/\[(\d+)m/', "\e[".'\1m', $string);
    }
}
