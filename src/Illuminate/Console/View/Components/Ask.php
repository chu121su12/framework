<?php

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Question\Question2;

class Ask extends Component
{
    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $question
     * @param  string  $default
     * @return mixed
     */
    public function render($question, $default = null, $multiline = false)
    {
        return $this->usingQuestionHelper(
            function () use ($question, $default, $multiline) { return $this->output->askQuestion(
                (new Question2($question, $default))
                    ->setMultiline($multiline)
            ); }
        );
    }
}
