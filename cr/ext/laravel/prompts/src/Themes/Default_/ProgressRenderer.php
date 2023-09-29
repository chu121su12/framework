<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\Progress;

class ProgressRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * The character to use for the progress bar.
     */
    protected /*string */$barCharacter = '█';

    /**
     * Render the progress bar.
     *
     * @param  Progress<int|iterable<mixed>>  $progress
     */
    public function __invoke(Progress $progress)/*: string*/
    {
        $filled = str_repeat($this->barCharacter, (int) ceil($progress->percentage() * min($this->minWidth, $progress->terminal()->cols() - 6)));

        switch ($progress->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                    $this->dim($filled),
                    /*$footer = */'',
                    /*$color = */'gray',
                    /*info: */$progress->progress.'/'.$progress->total
                );

            case 'error': return $this
                ->box(
                    $this->truncate($progress->label, $progress->terminal()->cols() - 6),
                    $this->dim($filled),
                    /*$footer = */'',
                    /*color: */'red',
                    /*info: */$progress->progress.'/'.$progress->total
                );

            case 'cancel': return $this
                ->box(
                    $this->truncate($progress->label, $progress->terminal()->cols() - 6),
                    $this->dim($filled),
                    /*$footer = */'',
                    /*color: */'red',
                    /*info: */$progress->progress.'/'.$progress->total
                )
                ->error('Cancelled.');

            default: return $this
                ->box(
                    $this->cyan($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                    $this->dim($filled),
                    /*$footer = */'',
                    /*$color = */'gray',
                    /*info: */$progress->progress.'/'.$progress->total
                )
                ->when(
                    $progress->hint,
                    function () use ($progress) { return $this->hint($progress->hint); },
                    function () { return $this->newLine(); } // Space for errors
                );
        }
    }
}
