<?php

namespace Laravel\Prompts\Themes\Default_\Concerns;

use Laravel\Prompts\Prompt;

trait DrawsBoxes
{
    use InteractsWithStrings;

    protected /*int */$minWidth = 60;

    /**
     * Draw a box.
     *
     * @return $this
     */
    protected function box(
        /*string */$title,
        /*string */$body,
        /*string */$footer = '',
        /*string */$color = 'gray',
        /*string */$info = ''
    )/*: self */{
        $title = backport_type_check('string', $title);
        $body = backport_type_check('string', $body);
        $footer = backport_type_check('string', $footer);
        $color = backport_type_check('string', $color);
        $info = backport_type_check('string', $info);

        $this->minWidth = min($this->minWidth, Prompt::terminal()->cols() - 6);

        $bodyLines = collect(explode(PHP_EOL, $body));
        $footerLines = collect(explode(PHP_EOL, $footer))->filter();
        $width = $this->longest(
            $bodyLines
                ->merge($footerLines)
                ->push($title)
                ->toArray()
        );

        $titleLength = mb_strwidth($this->stripEscapeSequences($title));
        $titleLabel = $titleLength > 0 ? " {$title} " : '';
        $topBorder = str_repeat('─', $width - $titleLength + ($titleLength > 0 ? 0 : 2));

        $this->line("{$this->{$color}(' ┌')}{$titleLabel}{$this->{$color}($topBorder.'┐')}");

        $bodyLines->each(function ($line) use ($width, $color) {
            $this->line("{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}");
        });

        if ($footerLines->isNotEmpty()) {
            $this->line($this->{$color}(' ├'.str_repeat('─', $width + 2).'┤'));

            $footerLines->each(function ($line) use ($width, $color) {
                $this->line("{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}");
            });
        }

        $this->line($this->{$color}(' └'.str_repeat(
            '─', $info ? ($width - mb_strwidth($this->stripEscapeSequences($info))) : ($width + 2)
        ).($info ? " {$info} " : '').'┘'));

        return $this;
    }
}
