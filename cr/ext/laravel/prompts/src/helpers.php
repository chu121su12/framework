<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

/**
 * Prompt the user for text input.
 */
function text(
    /*string */$label,
    /*string */$placeholder = '',
    /*string */$default = '',
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = ''
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $default = backport_type_check('string', $default);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new TextPrompt($label, $placeholder, $default, $required, $validate, $hint))->prompt();
}

/**
 * Prompt the user for input, hiding the value.
 */
function password(
    /*string */$label,
    /*string */$placeholder = '',
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = ''
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new PasswordPrompt($label, $placeholder, $required, $validate, $hint))->prompt();
}

/**
 * Prompt the user to select an option.
 *
 * @param  array<int|string, string>|Collection<int|string, string>  $options
 */
function select(
    /*string */$label,
    /*array|Collection */$options,
    /*int|string */$default = null,
    /*int */$scroll = 5,
    Closure $validate = null,
    /*string */$hint = '',
    /*bool|string */$required = true
)/*: int|string*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check(['array', Collection::class], $options);
    $default = backport_type_check('int|string', $default);
    $scroll = backport_type_check('int', $scroll);
    $hint = backport_type_check('string', $hint);
    $required = backport_type_check('bool|string', $required);

    return (new SelectPrompt($label, $options, $default, $scroll, $validate, $hint, $required))->prompt();
}

/**
 * Prompt the user to select multiple options.
 *
 * @param  array<int|string, string>|Collection<int|string, string>  $options
 * @param  array<int|string>|Collection<int, int|string>  $default
 * @return array<int|string>
 */
function multiselect(
    /*string */$label,
    /*array|Collection */$options,
    /*array|Collection */$default = [],
    /*int */$scroll = 5,
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = 'Use the space bar to select options.'
)/*: array*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check(['array', Collection::class], $options);
    $default = backport_type_check(['array', Collection::class], $default);
    $scroll = backport_type_check('int', $scroll);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new MultiSelectPrompt($label, $options, $default, $scroll, $required, $validate, $hint))->prompt();
}

/**
 * Prompt the user to confirm an action.
 */
function confirm(
    /*string */$label,
    /*bool */$default = true,
    /*string */$yes = 'Yes',
    /*string */$no = 'No',
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = ''
)/*: bool*/
{
    $label = backport_type_check('string', $label);
    $default = backport_type_check('bool', $default);
    $yes = backport_type_check('string', $yes);
    $no = backport_type_check('string', $no);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new ConfirmPrompt($label, $default, $yes, $no, $required, $validate, $hint))->prompt();
}

/**
 * Prompt the user for text input with auto-completion.
 *
 * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
 */
function suggest(
    /*string */$label,
    /*array|Collection|Closure */$options,
    /*string */$placeholder = '',
    /*string */$default = '',
    /*int */$scroll = 5,
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = ''
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check(['array', Collection::class, Closure::class], $options);
    $placeholder = backport_type_check('string', $placeholder);
    $default = backport_type_check('string', $default);
    $scroll = backport_type_check('int', $scroll);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $required, $validate, $hint))->prompt();
}

/**
 * Allow the user to search for an option.
 *
 * @param  Closure(string): array<int|string, string>  $options
 */
function search(
    /*string */$label,
    Closure $options,
    /*string */$placeholder = '',
    /*int */$scroll = 5,
    Closure $validate = null,
    /*string */$hint = '',
    /*bool|string */$required = true
)/*: int|string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $scroll = backport_type_check('int', $scroll);
    $hint = backport_type_check('string', $hint);
    $required = backport_type_check('bool|string', $required);

    return (new SearchPrompt($label, $options, $placeholder, $scroll, $validate, $hint, $required))->prompt();
}

/**
 * Allow the user to search for multiple option.
 *
 * @param  Closure(string): array<int|string, string>  $options
 * @return array<int|string>
 */
function multisearch(
    /*string */$label,
    Closure $options,
    /*string */$placeholder = '',
    /*int */$scroll = 5,
    /*bool|string */$required = false,
    Closure $validate = null,
    /*string */$hint = 'Use the space bar to select options.'
)/*: array*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $scroll = backport_type_check('int', $scroll);
    $required = backport_type_check('bool|string', $required);
    $hint = backport_type_check('string', $hint);

    return (new MultiSearchPrompt($label, $options, $placeholder, $scroll, $required, $validate, $hint))->prompt();
}

/**
 * Render a spinner while the given callback is executing.
 *
 * @template TReturn of mixed
 *
 * @param  \Closure(): TReturn  $callback
 * @return TReturn
 */
function spin(
    Closure $callback,
    /*string */$message = ''
)/*: mixed*/
{
    $message = backport_type_check('string', $message);

    return (new Spinner($message))->spin($callback);
}

/**
 * Display a note.
 */
function note(
    /*string */$message,
    /*string */$type = null
)/*: void*/
{
    $message = backport_type_check('string', $message);
    $type = backport_type_check('string', $type);

    (new Note($message, $type))->display();
}

/**
 * Display an error.
 */
function error(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'error'))->display();
}

/**
 * Display a warning.
 */
function warning(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'warning'))->display();
}

/**
 * Display an alert.
 */
function alert(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'alert'))->display();
}

/**
 * Display an informational message.
 */
function info(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'info'))->display();
}

/**
 * Display an introduction.
 */
function intro(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'intro'))->display();
}

/**
 * Display a closing message.
 */
function outro(/*string */$message)/*: void*/
{
    $message = backport_type_check('string', $message);

    (new Note($message, 'outro'))->display();
}

/**
 * Display a table.
 *
 * @param  array<int, string|array<int, string>>|Collection<int, string|array<int, string>>  $headers
 * @param  array<int, array<int, string>>|Collection<int, array<int, string>>  $rows
 */
function table(/*array|Collection */$headers = [], /*array|Collection */$rows = null)/*: void*/
{
    $headers = backport_type_check(['array', Collection::class], $headers);
    $rows = backport_type_check(['array', Collection::class], $rows);

    (new Table($headers, $rows))->display();
}

/**
 * Display a progress bar.
 *
 * @template TSteps of iterable<mixed>|int
 * @template TReturn
 *
 * @param  TSteps  $steps
 * @param  ?Closure((TSteps is int ? int : value-of<TSteps>), Progress<TSteps>): TReturn  $callback
 * @return ($callback is null ? Progress<TSteps> : array<TReturn>)
 */
function progress(
    /*string */$label,
    /*iterable|int */$steps,
    Closure $callback = null,
    /*string */$hint = ''
)/*: array|Progress*/
{
    $label = backport_type_check('string', $label);
    $steps = backport_type_check('iterable|int', $steps);
    $hint = backport_type_check('string', $hint);

    $progress = new Progress($label, $steps, $hint);

    if ($callback !== null) {
        return $progress->map($callback);
    }

    return $progress;
}
