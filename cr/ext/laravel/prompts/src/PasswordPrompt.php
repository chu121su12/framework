<?php

namespace Laravel\Prompts;

use Closure;

class PasswordPrompt extends Prompt
{
    use Concerns\TypedValue;

    public /*string */$label;
    public /*string */$placeholder;
    public /*bool|string */$required;
    public /*?Closure */$validate;

    /**
     * Create a new PasswordPrompt instance.
     */
    public function __construct(
        /*public string */$label,
        /*public string */$placeholder = '',
        /*public bool|string */$required = false,
        /*public *//*?*/Closure $validate = null
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = $validate;

        $this->trackTypedValue();
    }

    /**
     * Get a masked version of the entered value.
     */
    public function masked()/*: string*/
    {
        return str_repeat('•', mb_strlen($this->value()));
    }

    /**
     * Get the masked value with a virtual cursor.
     */
    public function maskedWithCursor(/*int */$maxWidth)/*: string*/
    {
        $maxWidth = backport_type_check('int', $maxWidth);

        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->masked(), $this->cursorPosition, $maxWidth);
    }
}
