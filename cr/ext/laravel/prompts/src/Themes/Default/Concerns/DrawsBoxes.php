<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Laravel\Prompts\Prompt;

trait DrawsBoxes
{
    protected /*int */$minWidth = 60;

    /**
     * Draw a box.
     */
    protected function box(
        /*string */$title,
        string $body,
        string $footer = '',
        string $color = 'gray'
    )/*: self */{
        $title = backport_type_check('string', $title);

        $this->minWidth = min($this->minWidth, Prompt::terminal()->cols() - 6);

        $bodyLines = collect(explode(PHP_EOL, $body));
        $footerLines = collect(explode(PHP_EOL, $footer))->filter();
        $width = $this->longest(
            $bodyLines
                ->merge($footerLines)
                ->push($title)
                ->toArray()
        );

        $topBorder = str_repeat('─', $width - mb_strlen($this->stripEscapeSequences($title)));
        $bottomBorder = str_repeat('─', $width + 2);

        $this->line("{$this->{$color}(' ┌')} {$title} {$this->{$color}($topBorder.'┐')}");

        $bodyLines->each(function ($line) use ($width, $color) {
            $this->line("{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}");
        });

        if ($footerLines->isNotEmpty()) {
            $this->line($this->{$color}(' ├'.$bottomBorder.'┤'));

            $footerLines->each(function ($line) use ($width, $color) {
                $this->line("{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}");
            });
        }

        $this->line($this->{$color}(' └'.$bottomBorder.'┘'));

        return $this;
    }

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
                ->map(function ($line) use ($padding) { return mb_strlen($this->stripEscapeSequences($line)) + $padding; })
                ->max()
        );
    }

    /**
     * Pad text ignoring ANSI escape sequences.
     */
    protected function pad(/*string */$text, /*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        $text = backport_type_check('string', $text);

        $rightPadding = str_repeat(' ', max(0, $length - mb_strlen($this->stripEscapeSequences($text))));

        return "{$text}{$rightPadding}";
    }

    /**
     * Strip ANSI escape sequences from the given text.
     */
    protected function stripEscapeSequences(/*string */$text)/*: string*/
    {
        $text = backport_type_check('string', $text);

        return preg_replace("/\e[^m]*m/", '', $text);
    }
}
