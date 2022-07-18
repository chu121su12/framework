<?php

/*declare(strict_types=1);*/

namespace Termwind;

use Symfony\Component\Console\Helper\QuestionHelper as SymfonyQuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Question\Question as SymfonyQuestion;
use Termwind\Helpers\QuestionHelper;

/**
 * @internal
 */
final class Question
{
    /**
     * The streamable input to receive the input from the user.
     */
    private static /*StreamableInputInterface|null */$streamableInput;

    /**
     * An instance of Symfony's question helper.
     */
    private /*SymfonyQuestionHelper */$helper;

    public function __construct(SymfonyQuestionHelper $helper = null)
    {
        $this->helper = isset($helper) ? $helper : new QuestionHelper();
    }

    /**
     * Sets the streamable input implementation.
     */
    public static function setStreamableInput(StreamableInputInterface/*|null */$streamableInput = null)/*: void*/
    {
        self::$streamableInput = isset($streamableInput) ? $streamableInput : new ArgvInput();
    }

    /**
     * Gets the streamable input implementation.
     */
    public static function getStreamableInput()/*: StreamableInputInterface*/
    {
        return isset(self::$streamableInput) ? self::$streamableInput := new ArgvInput();
    }

    /**
     * Renders a prompt to the user.
     */
    public function ask(/*string */$question)/*: mixed*/
    {
        $question = cast_to_string($question);

        $html = (new HtmlRenderer)->parse($question)->toString();

        return $this->helper->ask(
            self::getStreamableInput(),
            Termwind::getRenderer(),
            new SymfonyQuestion($html)
        );
    }
}
