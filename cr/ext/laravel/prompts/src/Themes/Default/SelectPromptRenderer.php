<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer extends Renderer
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
                    $this->truncate($this->format($prompt->label()), $maxWidth)
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
    protected function renderOptions(SelectPrompt $prompt)/*: string*/
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(function ($label) use ($prompt) {
                    return $this->truncate($this->format($label), $prompt->terminal()->cols() - 12);
                })
                ->map(function ($label, $i) use ($prompt) {
                    if ($prompt->state === 'cancel') {
                        return $this->dim($prompt->highlighted === $i
                            ? "› ● {$this->strikethrough($label)}  "
                            : "  ○ {$this->strikethrough($label)}  "
                        );
                    }

                    return $prompt->highlighted === $i
                        ? "{$this->cyan('›')} {$this->cyan('●')} {$label}  "
                        : "  {$this->dim('○')} {$this->dim($label)}  ";
                }),
            $prompt->highlighted,
            min($prompt->scroll, $prompt->terminal()->lines() - 5),
            min($this->longest($prompt->options, padding: 6), $prompt->terminal()->cols() - 6),
            $prompt->state === 'cancel' ? 'dim' : 'cyan'
        )->implode(PHP_EOL);
    }
}
