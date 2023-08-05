<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Illuminate\Support\Collection;

trait DrawsScrollbars
{
    /**
     * Scroll the given lines.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $lines
     * @return  \Illuminate\Support\Collection<int, string>
     */
    protected function scroll(Collection $lines, /*?int */$focused = null, /*int */$height, /*int */$width, /*string */$color = 'cyan')/*: Collection*/
    {
        $color = backport_type_check('string', $color);

        $width = backport_type_check('int', $width);

        $height = backport_type_check('int', $height);

        $focused = backport_type_check('?int', $focused);

        if ($lines->count() <= $height) {
            return $lines;
        }

        $visible = $this->visible($lines, $focused, $height);

        return $visible
            ->map(fn ($line) => $this->pad($line, $width))
            ->map(fn ($line, $index) => match (true) {
                $index === $this->scrollPosition($visible, $focused, $height, $lines->count()) => preg_replace('/.$/', $this->{$color}('┃'), $line),
                default => preg_replace('/.$/', $this->gray('│'), $line),
            });
    }

    /**
     * Get a scrolled version of the items.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $lines
     * @return  \Illuminate\Support\Collection<int, string>
     */
    protected function visible(Collection $lines, /*?int */$focused = null, /*int */$height)/*: Collection*/
    {
        $height = backport_type_check('int', $height);

        $focused = backport_type_check('?int', $focused);

        if ($lines->count() <= $height) {
            return $lines;
        }

        if ($focused === null || $focused < $height) {
            return $lines->slice(0, $height);
        }

        return $lines->slice($focused - $height + 1, $height);
    }

    /**
     * Scroll the given lines.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $visible
     */
    protected function scrollPosition(Collection $visible, /*?int */$focused = null, /*int */$height, /*int */$total)/*: int*/
    {
        $total = backport_type_check('int', $total);

        $height = backport_type_check('int', $height);

        $focused = backport_type_check('?int', $focused);

        if ($focused < $height) {
            return 0;
        }

        if ($focused === $total - 1) {
            return $total - 1;
        }

        $percent = ($focused + 1 - $height) / ($total - $height);

        $keys = $visible->slice(1, -1)->keys();
        $position = (int) ceil($percent * count($keys) - 1);

        return isset($keys[$position]) ? $keys[$position] : 0;
    }
}
