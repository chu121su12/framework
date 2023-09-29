<?php

namespace Laravel\Prompts\Themes\Default_\Concerns;

use Illuminate\Support\Collection;

trait DrawsScrollbars
{
    /**
     * Render a scrollbar beside the visible items.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $visible
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function scrollbar(Collection $visible, /*int */$firstVisible, /*int */$height, /*int */$total, /*int */$width, /*string */$color = 'cyan')/*: Collection*/
    {
        $color = backport_type_check('string', $color);

        $width = backport_type_check('int', $width);

        $total = backport_type_check('int', $total);

        $height = backport_type_check('int', $height);

        $firstVisible = backport_type_check('int', $firstVisible);

        if ($height >= $total) {
            return $visible;
        }

        $scrollPosition = $this->scrollPosition($firstVisible, $height, $total);

        return $visible
            ->values()
            ->map(function ($line) use ($width) { return $this->pad($line, $width); })
            ->map(function ($line, $index) use ($scrollPosition, $color) {
                switch ($index) {
                    case $scrollPosition: return preg_replace('/.$/', $this->{$color}('┃'), $line);
                    default: return preg_replace('/.$/', $this->gray('│'), $line);
                }
            });
    }

    /**
     * Return the position where the scrollbar "handle" should be rendered.
     */
    protected function scrollPosition(/*int */$firstVisible, /*int */$height, /*int */$total)/*: int*/
    {
        $total = backport_type_check('int', $total);

        $height = backport_type_check('int', $height);

        $firstVisible = backport_type_check('int', $firstVisible);

        if ($firstVisible === 0) {
            return 0;
        }

        $maxPosition = $total - $height;

        if ($firstVisible === $maxPosition) {
            return $height - 1;
        }

        if ($height <= 2) {
            return -1;
        }

        $percent = $firstVisible / $maxPosition;

        return (int) round($percent * ($height - 3)) + 1;
    }
}
