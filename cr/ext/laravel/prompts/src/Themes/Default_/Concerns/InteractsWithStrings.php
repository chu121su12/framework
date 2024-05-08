<?php

namespace Laravel\Prompts\Themes\Default_\Concerns;

trait InteractsWithStrings
{
    /**
     * Get the length of the longest line.
     *
     * @param  array<string>  $lines
     */
    protected function longest(array $lines, /*int */$padding = 0)/*: int*/
    {
        $padding = backport_type_check('int', $padding);

        return max(
            $this->minWidth,
            collect($lines)
                ->map(function ($line) use ($padding) {
                    return mb_strwidth($this->stripEscapeSequences($line)) + $padding;
                })
                ->max()
        );
    }

    /**
     * Pad text ignoring ANSI escape sequences.
     */
    protected function pad(/*string */$text, /*int */$length, /*string */$char = ' ')/*: string*/
    {
        $char = backport_type_check('string', $char);

        $length = backport_type_check('int', $length);

        $text = backport_type_check('string', $text);

        $rightPadding = str_repeat($char, max(0, $length - mb_strwidth($this->stripEscapeSequences($text))));

        return "{$text}{$rightPadding}";
    }

    /**
     * Strip ANSI escape sequences from the given text.
     */
    protected function stripEscapeSequences(/*string */$text)/*: string*/
    {
        $text = backport_type_check('string', $text);

        // Strip ANSI escape sequences.
        $text = preg_replace("/\e[^m]*m/", '', $text);

        // Strip Symfony named style tags.
        $text = preg_replace("/<(info|comment|question|error)>(.*?)<\/\\1>/", '$2', $text);

        // Strip Symfony inline style tags.
        return preg_replace("/<(?:(?:[fb]g|options)=[a-z,;]+)+>(.*?)<\/>/i", '$1', $text);
    }
}
