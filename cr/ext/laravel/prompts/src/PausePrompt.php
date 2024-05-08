<?php

namespace Laravel\Prompts;

class PausePrompt extends Prompt
{
    public $message;

    /**
     * Create a new PausePrompt instance.
     */
    public function __construct(/*public *//*string */$message = 'Press enter to continue...')
    {
        $this->message = backport_type_check('string', $message);

        $this->required = false;
        $this->validate = null;

        $this->on('key', function ($key) {
            switch ($key) {
                case Key::ENTER: return $this->submit();
                default: return null;
            }
        });
    }

    /**
     * Get the value of the prompt.
     */
    public function value()/*: bool*/
    {
        return static::$interactive;
    }
}
