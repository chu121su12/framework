<?php

namespace Laravel\Prompts;

class Note extends Prompt
{
    public /*string */$message;
    public /*?string */$type;

    /**
     * Create a new Note instance.
     */
    public function __construct(/*public string */$message, /*public ?string */$type = null)
    {
        $this->message = backport_type_check('string', $message);
        $this->type = backport_type_check('?string', $type);

        //
    }

    /**
     * Display the note.
     */
    public function display()/*: void*/
    {
        $this->prompt();
    }

    /**
     * Display the note.
     */
    public function prompt()/*: bool*/
    {
        $this->capturePreviousNewLines();

        $this->state = 'submit';

        static::outputWrite($this->renderTheme());

        return true;
    }

    /**
     * Get the value of the prompt.
     */
    public function value()/*: bool*/
    {
        return true;
    }
}
