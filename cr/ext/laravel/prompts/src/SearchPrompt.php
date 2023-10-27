<?php

namespace Laravel\Prompts;

use Closure;
use InvalidArgumentException;

class SearchPrompt extends Prompt
{
    use Concerns\Scrolling;
    use Concerns\Truncation;
    use Concerns\TypedValue;

    /**
     * The cached matches.
     *
     * @var array<int|string, string>|null
     */
    protected /*?array */$matches = null;

    public /*string */$label;
    public /*Closure */$options;
    public /*string */$placeholder;
    public /*int */$scroll;
    public /*?Closure */$validate;
    public /*string */$hint;
    public /*bool|string */$required;

    /**
     * Create a new SearchPrompt instance.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        /*public string */$label,
        /*public */Closure $options,
        /*public string */$placeholder = '',
        /*public int */$scroll = 5,
        /*public *//*?*/Closure $validate = null,
        /*public string */$hint = '',
        /*public bool|string */$required = ''
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->scroll = backport_type_check('int', $scroll);
        $this->hint = backport_type_check('string', $hint);
        $this->required = backport_type_check('bool|string', $required);
        $this->options = $options;
        $this->validate = $validate;

        if ($this->required === false) {
            throw new InvalidArgumentException('Argument [required] must be true or a string.');
        }

        $this->trackTypedValue(
            /*$default = */'',
            /*submit: */false,
            /*ignore: */function ($key) {
                return Key::oneOf([Key::HOME, Key::END, Key::CTRL_A, Key::CTRL_E], $key) && $this->highlighted !== null;
            }
        );

    
        $this->reduceScrollingToFitTerminal();

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::SHIFT_TAB: 
            case Key::CTRL_P: return $this->highlightPrevious(count($this->matches), true);

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::TAB: 
            case Key::CTRL_N: return $this->highlightNext(count($this->matches), true);

            case Key::oneOf([Key::HOME, Key::CTRL_A], $key): return $this->highlighted !== null ? $this->highlight(0) : null;
            case Key::oneOf([Key::END, Key::CTRL_E], $key): return $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null;

            case Key::ENTER: return $this->highlighted !== null
                ? $this->submit()
                : $this->search();

            case Key::oneOf([Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_B, Key::CTRL_F], $key): return $this->highlighted = null;

            default: return $this->search();
        } });
    }

    /**
     * Perform the search.
     */
    protected function search()/*: void*/
    {
        $this->state = 'searching';
        $this->highlighted = null;
        $this->render();
        $this->matches = null;
        $this->firstVisible = 0;
        $this->state = 'active';
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(/*int */$maxWidth)/*: string*/
    {
        $maxWidth = backport_type_check('int', $maxWidth);

        if ($this->highlighted !== null) {
            return $this->typedValue === ''
                ? $this->dim($this->truncate($this->placeholder, $maxWidth))
                : $this->truncate($this->typedValue, $maxWidth);
        }

        if ($this->typedValue === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->typedValue, $this->cursorPosition, $maxWidth);
    }

    /**
     * Get options that match the input.
     *
     * @return array<string>
     */
    public function matches()/*: array*/
    {
        if (is_array($this->matches)) {
            return $this->matches;
        }

        return $this->matches = call_user_func($this->options, $this->typedValue);
    }

    /**
     * The currently visible matches.
     *
     * @return array<string>
     */
    public function visible()/*: array*/
    {
        return array_slice(
            $this->matches(),
            $this->firstVisible,
            $this->scroll,
            /*preserve_keys: */true
        );
    }

    /**
     * Get the current search query.
     */
    public function searchValue()/*: string*/
    {
        return $this->typedValue;
    }

    /**
     * Get the selected value.
     */
    public function value()/*: int|string|null*/
    {
        if ($this->matches === null || $this->highlighted === null) {
            return null;
        }

        return array_is_list($this->matches)
            ? $this->matches[$this->highlighted]
            : array_keys($this->matches)[$this->highlighted];
    }

    /**
     * Get the selected label.
     */
    public function label()/*: ?string*/
    {
        $matchKey = array_keys($this->matches)[$this->highlighted];

        return isset($this->matches[$matchKey]) ? $this->matches[$matchKey] : null;
    }
}
