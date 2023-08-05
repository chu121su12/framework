<?php

namespace Laravel\Prompts;

use Closure;

class TextPrompt extends Prompt
{
    use Concerns\TypedValue;

    public /*string */$label;
    public /*string */$placeholder;
    public /*string */$default;
    public /*bool|string */$required;
    public /*?Closure */$validate;

    /**
     * Create a new TextPrompt instance.
     */
    public function __construct(
        /*public string */$label,
        /*public string */$placeholder = '',
        /*public string */$default = '',
        /*public bool|string */$required = false,
        /*public *//*?*/Closure $validate = null
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->default = backport_type_check('string', $default);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = $validate;

        $this->trackTypedValue($default);
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(/*int */$maxWidth)/*: string*/
    {
        $maxWidth = backport_type_check('int', $maxWidth);

        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->value(), $this->cursorPosition, $maxWidth);
    }
}
