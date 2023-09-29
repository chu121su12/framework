<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;

trait Interactivity
{
    /**
     * Whether to render the prompt interactively.
     */
    protected static /*bool */$interactive;

    /**
     * Set interactive mode.
     */
    public static function interactive(/*bool */$interactive = true)/*: void*/
    {
        $interactive = backport_type_check('bool', $interactive);

        static::$interactive = $interactive;
    }

    /**
     * Return the default value if it passes validation.
     */
    protected function default_()/*: mixed*/
    {
        $default = $this->value();

        $this->validate($default);

        if ($this->state === 'error') {
            throw new NonInteractiveValidationException($this->error);
        }

        return $default;
    }
}
