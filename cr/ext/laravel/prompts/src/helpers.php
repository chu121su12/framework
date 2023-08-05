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
    Closure $validate = null
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $default = backport_type_check('string', $default);
    $required = backport_type_check('bool|string', $required);

    return (new TextPrompt($label, $placeholder, $default, $required, $validate))->prompt();
}

/**
 * Prompt the user for input, hiding the value.
 */
function password(
    /*string */$label,
    /*string */$placeholder = '',
    /*bool|string */$required = false,
    Closure $validate = null
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $required = backport_type_check('bool|string', $required);

    return (new PasswordPrompt($label, $placeholder, $required, $validate))->prompt();
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
    Closure $validate = null
)/*: int|string*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check('array|Collection', $options);
    $default = backport_type_check('int|string', $default);
    $scroll = backport_type_check('int', $scroll);

    return (new SelectPrompt($label, $options, $default, $scroll, $validate))->prompt();
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
    Closure $validate = null
)/*: array*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check('array|Collection', $options);
    $default = backport_type_check('array|Collection', $default);
    $scroll = backport_type_check('int', $scroll);
    $required = backport_type_check('bool|string', $required);

    return (new MultiSelectPrompt($label, $options, $default, $scroll, $required, $validate))->prompt();
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
    Closure $validate = null
)/*: bool*/
{
    $label = backport_type_check('string', $label);
    $default = backport_type_check('bool', $default);
    $yes = backport_type_check('string', $yes);
    $no = backport_type_check('string', $no);
    $required = backport_type_check('bool|string', $required);

    return (new ConfirmPrompt($label, $default, $yes, $no, $required, $validate))->prompt();
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
    Closure $validate = null
)/*: string*/
{
    $label = backport_type_check('string', $label);
    $options = backport_type_check('array|Collection|Closure', $options);
    $placeholder = backport_type_check('string', $placeholder);
    $default = backport_type_check('string', $default);
    $scroll = backport_type_check('int', $scroll);
    $required = backport_type_check('bool|string', $required);

    return (new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $required, $validate))->prompt();
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
    Closure $validate = null
)/*: int|string*/
{
    $label = backport_type_check('string', $label);
    $placeholder = backport_type_check('string', $placeholder);
    $scroll = backport_type_check('int', $scroll);

    return (new SearchPrompt($label, $options, $placeholder, $scroll, $validate))->prompt();
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
