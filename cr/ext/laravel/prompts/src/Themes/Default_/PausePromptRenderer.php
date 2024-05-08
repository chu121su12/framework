<?php

namespace Laravel\Prompts\Themes\Default_;

use Laravel\Prompts\PausePrompt;

class PausePromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the pause prompt.
     */
    public function __invoke(PausePrompt $prompt)/*: string*/
    {
        switch ($prompt->state) {
            case 'submit': collect(explode(PHP_EOL, $prompt->message))
                ->each(function ($line) { return $this->line($this->gray(" {$line}")); });
                break;

            default: collect(explode(PHP_EOL, $prompt->message))
                ->each(function ($line) { return $this->line($this->green(" {$line}")); });
        };

        return $this;
    }
}
