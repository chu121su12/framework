<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class SelectPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the select prompt.
     */
    public function __invoke(SelectPrompt $prompt)/*: string*/
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        switch ($prompt->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->truncate($prompt->label(), $maxWidth)
                );

            case 'cancel': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    /*$footer = */'',
                    /*color: */'red'
                )
                ->error('Cancelled.');

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    /*$footer = */'',
                    /*color: */'yellow'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderOptions($prompt)
                )
                ->when(
                    $prompt->hint,
                    function () use ($prompt) { return $this->hint($prompt->hint); },
                    function () { return $this->newLine(); } // Space for errors
                );
        }
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SelectPrompt $prompt)/*: string*/
    {
        return $this->scrollbar(
            collect($prompt->visible())
                ->map(function ($label) use ($prompt) { return $this->truncate($label, $prompt->terminal()->cols() - 12); })
                ->map(function ($label, $key) use ($prompt) {
                    $index = array_search($key, array_keys($prompt->options));

                    if ($prompt->state === 'cancel') {
                        return $this->dim($prompt->highlighted === $index
                            ? "› ● {$this->strikethrough($label)}  "
                            : "  ○ {$this->strikethrough($label)}  "
                        );
                    }

                    return $prompt->highlighted === $index
                        ? "{$this->cyan('›')} {$this->cyan('●')} {$label}  "
                        : "  {$this->dim('○')} {$this->dim($label)}  ";
                })
                ->values(),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->options),
            min($this->longest($prompt->options, /*padding: */6), $prompt->terminal()->cols() - 6),
            $prompt->state === 'cancel' ? 'dim' : 'cyan'
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
