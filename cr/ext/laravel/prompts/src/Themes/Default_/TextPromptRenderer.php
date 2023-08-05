<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\TextPrompt;

class TextPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the text prompt.
     */
    public function __invoke(TextPrompt $prompt)/*: string*/
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
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim($this->truncate($prompt->value() ?: $prompt->placeholder, $maxWidth))),
                    color: 'red'
                )
                ->error('Cancelled.');

            case 'error': return $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $prompt->valueWithCursor($maxWidth),
                    color: 'yellow'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5));

            default: return $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->valueWithCursor($maxWidth)
                )
                ->newLine(); // Space for errors
        }
    }
}
