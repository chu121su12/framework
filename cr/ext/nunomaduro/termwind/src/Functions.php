<?php

/*declare(strict_types=1);*/

namespace Termwind;

use Closure;
use Symfony\Component\Console\Output\OutputInterface;
use Termwind\Repositories\Styles as StyleRepository;
use Termwind\ValueObjects\Style;
use Termwind\ValueObjects\Styles;

if (! function_exists('Termwind\renderUsing')) {
    /**
     * Sets the renderer implementation.
     */
    function renderUsing(OutputInterface/*|null */$renderer = null)/*: void*/
    {
        Termwind::renderUsing($renderer);
    }
}

if (! function_exists('Termwind\style')) {
    /**
     * Creates a new style.
     *
     * @param (Closure(Styles $renderable, string|int ...$arguments): Styles)|null $callback
     */
    function style(/*string */$name, Closure $callback = null)/*: Style*/
    {
        $name = backport_type_check('string', $name);

        return StyleRepository::create($name, $callback);
    }
}

if (! function_exists('Termwind\render')) {
    /**
     * Render HTML to a string.
     */
    function render(/*string */$html, /*int */$options = OutputInterface::OUTPUT_NORMAL)/*: void*/
    {
        $html = backport_type_check('string', $html);

        $options = backport_type_check('int', $options);

        (new HtmlRenderer)->render($html, $options);
    }
}

if (! function_exists('Termwind\terminal')) {
    /**
     * Returns a Terminal instance.
     */
    function terminal()/*: Terminal*/
    {
        return new Terminal;
    }
}

if (! function_exists('Termwind\ask')) {
    /**
     * Renders a prompt to the user.
     */
    function ask(/*string */$question)/*: mixed*/
    {
        $question = backport_type_check('string', $question);

        return (new Question)->ask($question);
    }
}
