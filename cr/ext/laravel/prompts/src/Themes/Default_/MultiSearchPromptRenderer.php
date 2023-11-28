<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class MultiSearchPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the suggest prompt.
     */
    public function __invoke(MultiSearchPrompt $prompt)/*: string*/
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        switch ($prompt->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderSelectedOptions($prompt)
                );

            case 'cancel': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->strikethrough($this->dim($this->truncate($prompt->searchValue() ?: $prompt->placeholder, $maxWidth))),
                    /*$footer = */'',
                    /*color: */'red'
                )
                ->error('Cancelled.');

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $prompt->valueWithCursor($maxWidth),
                    $this->renderOptions($prompt),
                    /*color: */'yellow',
                    /*info: */$this->getInfoText($prompt)
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            case 'searching': return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->valueWithCursorAndSearchIcon($prompt, $maxWidth),
                    $this->renderOptions($prompt),
                    /*$color = */'gray',
                    /*info: */$this->getInfoText($prompt)
                )
                ->hint($prompt->hint);

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->valueWithCursor($maxWidth),
                    $this->renderOptions($prompt),
                    /*$color = */'gray',
                    /*info: */$this->getInfoText($prompt)
                )
                ->when(
                    $prompt->hint,
                    function () use ($prompt) { return $this->hint($prompt->hint); },
                    function () { return $this->newLine(); } // Space for errors
                )
                ->spaceForDropdown($prompt);
        }
    }

    /**
     * Render the value with the cursor and a search icon.
     */
    protected function valueWithCursorAndSearchIcon(MultiSearchPrompt $prompt, int $maxWidth)/*: string*/
    {
        return preg_replace(
            '/\s$/',
            $this->cyan('…'),
            $this->pad($prompt->valueWithCursor($maxWidth - 1).'  ', min($this->longest($prompt->matches(), /*padding: */2), $maxWidth))
        );
    }

    /**
     * Render a spacer to prevent jumping when the suggestions are displayed.
     */
    protected function spaceForDropdown(MultiSearchPrompt $prompt)/*: self*/
    {
        if ($prompt->searchValue() !== '') {
            return $this;
        }

        $this->newLine(max(
            0,
            min($prompt->scroll, $prompt->terminal()->lines() - 7) - count($prompt->matches())
        ));

        if ($prompt->matches() === []) {
            $this->newLine();
        }

        return $this;
    }

    /**
     * Render the options.
     */
    protected function renderOptions(MultiSearchPrompt $prompt)/*: string*/
    {
        if ($prompt->searchValue() !== '' && empty($prompt->matches())) {
            return $this->gray('  '.($prompt->state === 'searching' ? 'Searching...' : 'No results.'));
        }

        return $this->scrollbar(
            collect($prompt->visible())
                ->map(function ($label) use ($prompt) { return $this->truncate($label, $prompt->terminal()->cols() - 10); })
                ->map(function ($label, $key) use ($prompt) {
                    $index = array_search($key, array_keys($prompt->matches()));
                    $active = $index === $prompt->highlighted;
                    $selected = array_is_list($prompt->visible())
                        ? in_array($label, $prompt->value())
                        : in_array($key, $prompt->value());

                    switch (true) {
                        case $active && $selected: return "{$this->cyan('› ◼')} {$label}  ";
                        case $active: return "{$this->cyan('›')} ◻ {$label}  ";
                        case $selected: return "  {$this->cyan('◼')} {$this->dim($label)}  ";
                        default: return "  {$this->dim('◻')} {$this->dim($label)}  ";
                    }
                }),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->matches()),
            min($this->longest($prompt->matches(), /*padding: */4), $prompt->terminal()->cols() - 6)
        )->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSearchPrompt $prompt)/*: string*/
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
     * Render the info text.
     */
    protected function getInfoText(MultiSearchPrompt $prompt)/*: string*/
    {
        $info = count($prompt->value()).' selected';

        $hiddenCount = count($prompt->value()) - collect($prompt->matches())
            ->filter(function ($label, $key) use ($prompt) { return in_array(array_is_list($prompt->matches()) ? $label : $key, $prompt->value()); })
            ->count();

        if ($hiddenCount > 0) {
            $info .= " ($hiddenCount hidden)";
        }

        return $info;
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines()/*: int*/
    {
        return 7;
    }
}
