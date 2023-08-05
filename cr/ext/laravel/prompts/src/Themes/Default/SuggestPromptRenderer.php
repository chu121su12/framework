<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\SuggestPrompt;

class SuggestPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the suggest prompt.
     */
    public function __invoke(SuggestPrompt $prompt)/*: string*/
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        switch ($prompt->state) {
            case 'submit': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->truncate($prompt->value(), $maxWidth)
                );

            case 'cancel': return $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->strikethrough($this->dim($this->truncate($prompt->value() ?: $prompt->placeholder, $maxWidth))),
                    color: 'red'
                )
                ->error('Cancelled');

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->valueWithCursorAndArrow($prompt, $maxWidth),
                    $this->renderOptions($prompt),
                    color: 'yellow'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->valueWithCursorAndArrow($prompt, $maxWidth),
                    $this->renderOptions($prompt)
                )
                ->spaceForDropdown($prompt)
                ->newLine(); // Space for errors
        }
    }

    /**
     * Render the value with the cursor and an arrow.
     */
    protected function valueWithCursorAndArrow(SuggestPrompt $prompt, /*int */$maxWidth)/*: string*/
    {
        $maxWidth = backport_type_check('int', $maxWidth);

        if ($prompt->highlighted !== null || $prompt->value() !== '' || count($prompt->matches()) === 0) {
            return $prompt->valueWithCursor($maxWidth);
        }

        return preg_replace(
            '/\s$/',
            $this->cyan('⌄'),
            $this->pad($prompt->valueWithCursor($maxWidth - 1).'  ', min($this->longest($prompt->matches(), /*padding: */2), $maxWidth))
        );
    }

    /**
     * Render a spacer to prevent jumping when the suggestions are displayed.
     */
    protected function spaceForDropdown(SuggestPrompt $prompt)/*: self*/
    {
        if ($prompt->value() === '' && $prompt->highlighted === null) {
            $this->newLine(min(
                count($prompt->matches()),
                $prompt->scroll,
                $prompt->terminal()->lines() - 7
            ) + 1);
        }

        return $this;
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SuggestPrompt $prompt)/*: string*/
    {
        if (empty($prompt->matches()) || ($prompt->value() === '' && $prompt->highlighted === null)) {
            return '';
        }

        return $this->scroll(
            collect($prompt->matches())
                ->map(function ($label) use ($prompt) {
                    return $this->truncate($label, $prompt->terminal()->cols() - 10);
                })
                ->map(function ($label, $i) use ($prompt) { return $prompt->highlighted === $i
                    ? "{$this->cyan('›')} {$label}  "
                    : "  {$this->dim($label)}  ";
                }),
            $prompt->highlighted,
            min($prompt->scroll, $prompt->terminal()->lines() - 7),
            min($this->longest($prompt->matches(), /*padding: */4), $prompt->terminal()->cols() - 6),
            $prompt->state === 'cancel' ? 'dim' : 'cyan'
        )->implode(PHP_EOL);
    }
}
