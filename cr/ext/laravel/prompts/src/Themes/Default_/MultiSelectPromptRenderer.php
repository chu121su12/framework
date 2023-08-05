<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer extends Renderer
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
                    color: 'red'
                )
                ->error('Cancelled.');

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    color: 'yellow'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderOptions($prompt)
                )
                ->newLine(); // Space for errors
        }
    }

    /**
     * Render the options.
     */
    protected function renderOptions(MultiSelectPrompt $prompt)/*: string*/
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(function ($label) use ($prompt) {
                    return $this->truncate($this->format($label), $prompt->terminal()->cols() - 12);
                })
                ->map(function ($label, $index) use ($prompt) {
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
                }),
            $prompt->highlighted,
            min($prompt->scroll, $prompt->terminal()->lines() - 5),
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
            function ($label) { return $this->truncate($this->format($label), $prompt->terminal()->cols() - 6); },
            $prompt->labels()
        ));
    }
}
