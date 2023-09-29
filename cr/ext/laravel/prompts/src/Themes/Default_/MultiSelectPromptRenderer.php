<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class MultiSelectPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the multiselect prompt.
     */
    public function __invoke(MultiSelectPrompt $prompt)/*: string*/
    {
        switch ($prompt->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderSelectedOptions($prompt)
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
                    /*color: */'yellow',
                    /*info: */count($prompt->options) > $prompt->scroll ? (count($prompt->value()).' selected') : ''
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderOptions($prompt),
                    /*$footer = */'',
                    /*$color = */'gray',
                    /*info: */count($prompt->options) > $prompt->scroll ? (count($prompt->value()).' selected') : ''
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
    protected function renderOptions(MultiSelectPrompt $prompt)/*: string*/
    {
        return $this->scrollbar(
            collect($prompt->visible())
                ->map(function ($label) use ($prompt) { return $this->truncate($label, $prompt->terminal()->cols() - 12); })
                ->map(function ($label, $key) use ($prompt) {
                    $index = array_search($key, array_keys($prompt->options));
                    $active = $index === $prompt->highlighted;
                    if (array_is_list($prompt->options)) {
                        $value = $prompt->options[$index];
                    } else {
                        $value = array_keys($prompt->options)[$index];
                    }
                    $selected = in_array($value, $prompt->value());

                    if ($prompt->state === 'cancel') {
                        switch (true) {
                            case $active && $selected:
                                $dim = "› ◼ {$this->strikethrough($label)}  ";
                                break;

                            case $active:
                                $dim = "› ◻ {$this->strikethrough($label)}  ";
                                break;

                            case $selected:
                                $dim = "  ◼ {$this->strikethrough($label)}  ";
                                break;

                            default:
                                $dim = "  ◻ {$this->strikethrough($label)}  ";
                        }

                        return $this->dim($dim);
                    }

                    switch (true) {
                        case $active && $selected: return "{$this->cyan('› ◼')} {$label}  ";
                        case $active: return "{$this->cyan('›')} ◻ {$label}  ";
                        case $selected: return "  {$this->cyan('◼')} {$this->dim($label)}  ";
                        default: return "  {$this->dim('◻')} {$this->dim($label)}  ";
                    }
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
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSelectPrompt $prompt)/*: string*/
    {
        if (count($prompt->labels()) === 0) {
            return $this->gray('None');
        }

        return implode("\n", array_map(
            function ($label) use ($prompt) { return $this->truncate($label, $prompt->terminal()->cols() - 6); },
            $prompt->labels()
        ));
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines()/*: int*/
    {
        return 5;
    }
}
