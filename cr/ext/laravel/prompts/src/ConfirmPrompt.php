<?php

namespace Laravel\Prompts;

use Closure;

class ConfirmPrompt extends Prompt
{
    /**
     * Whether the prompt has been confirmed.
     */
    public /*bool */$confirmed;

    /**
     * Create a new ConfirmPrompt instance.
     */
    public function __construct(
        public string $label,
        public bool $default = true,
        public string $yes = 'Yes',
        public string $no = 'No',
        public bool|string $required = false,
        public ?Closure $validate = null
    ) {
        $this->confirmed = $default;

        $this->on('key', function ($key) { switch ($key) {
            case 'y': return $this->confirmed = true;
            case 'n': return $this->confirmed = false;

            case Key::TAB:
            case Key::UP:
            case Key::DOWN:
            case Key::LEFT:
            case Key::RIGHT:
            case 'h':
            case 'j':
            case 'k':
            case 'l': return $this->confirmed = ! $this->confirmed;

            case Key::ENTER: return $this->submit();
            default: return null;
        } });
    }

    /**
     * Get the value of the prompt.
     */
    public function value()/*: bool*/
    {
        return $this->confirmed;
    }

    /**
     * Get the label of the selected option.
     */
    public function label()/*: string*/
    {
        return $this->confirmed ? $this->yes : $this->no;
    }
}
