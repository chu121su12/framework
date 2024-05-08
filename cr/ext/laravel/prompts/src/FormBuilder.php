<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;
use Laravel\Prompts\Exceptions\FormRevertedException;

class FormBuilder
{
    /**
     * Each step that should be executed.
     *
     * @var array<int, \Laravel\Prompts\FormStep>
     */
    protected /*array */$steps = [];

    /**
     * The responses provided by each step.
     *
     * @var array<mixed>
     */
    protected /*array */$responses = [];

    /**
     * Add a new step.
     */
    public function add(
        Closure $step,
        /*?string */$name = null,
        /*bool */$ignoreWhenReverting = false
    )/*: self*/
    {
        $ignoreWhenReverting = backport_type_check('bool', $ignoreWhenReverting);

        $name = backport_type_check('?string', $name);

        $this->steps[] = new FormStep($step, true, $name, $ignoreWhenReverting);

        return $this;
    }

    /**
     * Run all of the given steps.
     *
     * @return array<mixed>
     */
    public function submit()/*: array*/
    {
        $index = 0;
        $wasReverted = false;

        while ($index < count($this->steps)) {
            $step = $this->steps[$index];

            if ($wasReverted && $index > 0 && $step->shouldIgnoreWhenReverting($this->responses)) {
                $index--;

                continue;
            }

            $wasReverted = false;

            $index > 0
                ? Prompt::revertUsing(function () use (&$wasReverted) {
                    $wasReverted = true;
                }) : Prompt::preventReverting();

            try {
                $responsesKey = isset($step->name) ? $step->name : $index;

                $this->responses[$responsesKey] = $step->run(
                    $this->responses,
                    isset($this->responses[$responsesKey]) ? $this->responses[$responsesKey] : null
                );
            } catch (FormRevertedException $_e) {
                $wasReverted = true;
            }

            $wasReverted ? $index-- : $index++;
        }

        Prompt::preventReverting();

        return $this->responses;
    }

    /**
     * Prompt the user for text input.
     */
    public function text(
        /*string */$label,
        /*string */$placeholder = '',
        /*string */$default = '',
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $placeholder = backport_type_check('string', $placeholder);
        $default = backport_type_check('string', $default);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return text(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user for multiline text input.
     */
    public function textarea(
        /*string */$label,
        /*string */$placeholder = '',
        /*string */$default = '',
        /*bool|string */$required = false,
        /*?Closure */$validate = null,
        /*string */$hint = '',
        /*int */$rows = 5,
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $placeholder = backport_type_check('string', $placeholder);
        $default = backport_type_check('string', $default);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('?Closure', $validate);
        $hint = backport_type_check('string', $hint);
        $rows = backport_type_check('int', $rows);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return textarea(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user for input, hiding the value.
     */
    public function password(
        /*string */$label,
        /*string */$placeholder = '',
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $placeholder = backport_type_check('string', $placeholder);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return password(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user to select an option.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     * @param  true|string  $required
     */
    public function select(
        /*string */$label,
        /*array|Collection */$options,
        /*int|string|null */$default = null,
        /*int */$scroll = 5,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*bool|string */$required = true,
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $options = backport_type_check('array|Collection', $options);
        $default = backport_type_check('int|string|null', $default);
        $scroll = backport_type_check('int', $scroll);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $required = backport_type_check('bool|string', $required);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return select(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user to select multiple options.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     * @param  array<int|string>|Collection<int, int|string>  $default
     */
    public function multiselect(
        /*string */$label,
        /*array|Collection */$options,
        /*array|Collection */$default = [],
        /*int */$scroll = 5,
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = 'Use the space bar to select options.',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $options = backport_type_check('array|Collection', $options);
        $default = backport_type_check('array|Collection', $default);
        $scroll = backport_type_check('int', $scroll);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return multiselect(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user to confirm an action.
     */
    public function confirm(
        /*string */$label,
        /*bool */$default = true,
        /*string */$yes = 'Yes',
        /*string */$no = 'No',
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $default = backport_type_check('bool', $default);
        $yes = backport_type_check('string', $yes);
        $no = backport_type_check('string', $no);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return confirm(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user to continue or cancel after pausing.
     */
    public function pause(
        /*string */$message = 'Press enter to continue...',
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return pause(...$args); }, get_defined_vars());
    }

    /**
     * Prompt the user for text input with auto-completion.
     *
     * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
     */
    public function suggest(
        /*string */$label,
        /*array|Collection|Closure */$options,
        /*string */$placeholder = '',
        /*string */$default = '',
        /*int */$scroll = 5,
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $options = backport_type_check('array|Collection|Closure', $options);
        $placeholder = backport_type_check('string', $placeholder);
        $default = backport_type_check('string', $default);
        $scroll = backport_type_check('int', $scroll);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return suggest(...$args); }, get_defined_vars());
    }

    /**
     * Allow the user to search for an option.
     *
     * @param  Closure(string): array<int|string, string>  $options
     * @param  true|string  $required
     */
    public function search(
        /*string */$label,
        /*Closure */$options,
        /*string */$placeholder = '',
        /*int */$scroll = 5,
        /*mixed */$validate = null,
        /*string */$hint = '',
        /*bool|string */$required = true,
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $options = backport_type_check('Closure', $options);
        $placeholder = backport_type_check('string', $placeholder);
        $scroll = backport_type_check('int', $scroll);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $required = backport_type_check('bool|string', $required);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return search(...$args); }, get_defined_vars());
    }

    /**
     * Allow the user to search for multiple option.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function multisearch(
        /*string ==*/$label,
        /*Closure */$options,
        /*string */$placeholder = '',
        /*int */$scroll = 5,
        /*bool|string */$required = false,
        /*mixed */$validate = null,
        /*string */$hint = 'Use the space bar to select options.',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $options = backport_type_check('Closure', $options);
        $placeholder = backport_type_check('string', $placeholder);
        $scroll = backport_type_check('int', $scroll);
        $required = backport_type_check('bool|string', $required);
        $validate = backport_type_check('mixed', $validate);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return multisearch(...$args); }, get_defined_vars());
    }

    /**
     * Render a spinner while the given callback is executing.
     *
     * @param  \Closure(): mixed  $callback
     */
    public function spin(
        /*Closure */$callback,
        /*string */$message = '',
        /*?string */$name = null
    )/*: self*/
    {
        $callback = backport_type_check('Closure', $callback);
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return spin(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display a note.
     */
    public function note(
        /*string */$message,
        /*?string */$type = null,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $type = backport_type_check('?string', $type);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return note(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display an error.
     */
    public function error(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return error(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display a warning.
     */
    public function warning(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return warning(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display an alert.
     */
    public function alert(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return alert(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display an informational message.
     */
    public function info(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return info(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display an introduction.
     */
    public function intro(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return intro(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display a closing message.
     */
    public function outro(
        /*string */$message,
        /*?string */$name = null
    )/*: self*/
    {
        $message = backport_type_check('string', $message);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return outro(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display a table.
     *
     * @param  array<int, string|array<int, string>>|Collection<int, string|array<int, string>>  $headers
     * @param  array<int, array<int, string>>|Collection<int, array<int, string>>  $rows
     */
    public function table(
        /*array|Collection */$headers = [],
        /*array|Collection|null */$rows = null,
        /*?string */$name = null
    )/*: self*/
    {
        $headers = backport_type_check('array|Collection', $headers);
        $rows = backport_type_check('array|Collection|null', $rows);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return table(...$args); }, get_defined_vars(), true);
    }

    /**
     * Display a progress bar.
     *
     * @template TSteps of iterable<mixed>|int
     * @template TReturn
     *
     * @param  TSteps  $steps
     * @param  ?Closure((TSteps is int ? int : value-of<TSteps>), Progress<TSteps>): TReturn  $callback
     */
    public function progress(
        /*string */$label,
        /*iterable|int */$steps,
        /*?Closure */$callback = null,
        /*string */$hint = '',
        /*?string */$name = null
    )/*: self*/
    {
        $label = backport_type_check('string', $label);
        $steps = backport_type_check('iterable|int', $steps);
        $callback = backport_type_check('?Closure', $callback);
        $hint = backport_type_check('string', $hint);
        $name = backport_type_check('?string', $name);

        return $this->runPrompt(static function (...$args) { return progress(...$args); }, get_defined_vars(), true);
    }

    /**
     * Execute the given prompt passing the given arguments.
     *
     * @param  array<mixed>  $arguments
     */
    protected function runPrompt(
        /*callable */$prompt,
        /*array */$arguments,
        /*bool */$ignoreWhenReverting = false
    )/*: self*/
    {
        $prompt = backport_type_check('callable', $prompt);
        $arguments = backport_type_check('array', $arguments);
        $ignoreWhenReverting = backport_type_check('bool', $ignoreWhenReverting);

        return $this->add(function (array $responses, /*mixed */$previousResponse) use ($prompt, $arguments) {
            $previousResponse = backport_type_check('mixed', $previousResponse);

            unset($arguments['name']);

            if (array_key_exists('default', $arguments) && $previousResponse !== null) {
                $arguments['default'] = $previousResponse;
            }

            return $prompt(...$arguments);
        }, /*name: */$arguments['name'], /*ignoreWhenReverting: */$ignoreWhenReverting);
    }
}
