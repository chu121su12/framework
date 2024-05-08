<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class TextareaPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the textarea prompt.
     */
    public function __invoke(TextareaPrompt $prompt)/*: string*/
    {
        $prompt->width = $prompt->terminal()->cols() - 8;

        switch ($prompt->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->width)),
                    collect($prompt->lines())->implode(PHP_EOL)
                );

            case 'cancel': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->width),
                    collect($prompt->lines())->map(function ($line) { return $this->strikethrough($this->dim($line)); })->implode(PHP_EOL),
                    /*$footer = */'',
                    /*color: */'red'
                )
                ->error($prompt->cancelMessage);

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->width),
                    $this->renderText($prompt),
                    /*$footer = */'',
                    /*color: */'yellow',
                    /*info: */'Ctrl+D to submit'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->width)),
                    $this->renderText($prompt),
                    /*$footer = */'',
                    /*$color = */'gray',
                    /*info: */'Ctrl+D to submit'
                )
                ->when(
                    $prompt->hint,
                    function () use ($prompt) { return $this->hint($prompt->hint); },
                    function () { return $this->newLine(); } // Space for errors
                );
        };
    }

    /**
     * Render the text in the prompt.
     */
    protected function renderText(TextareaPrompt $prompt)/*: string*/
    {
        $visible = collect($prompt->visible());

        while ($visible->count() < $prompt->scroll) {
            $visible->push('');
        }

        $longest = $this->longest($prompt->lines()) + 2;

        return $this->scrollbar(
            $visible,
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->lines()),
            min($longest, $prompt->width + 2)
        )->implode(PHP_EOL);
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines()/*: int*/
    {
        return 5;
    }
}
