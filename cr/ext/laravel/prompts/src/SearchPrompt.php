<?php

namespace Laravel\Prompts;

use Closure;
use InvalidArgumentException;

class SearchPrompt extends Prompt
{
    use Concerns\TypedValue;

    /**
     * The index of the highlighted option.
     */
    public /*?int */$highlighted = null;

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

    /**
     * Create a new SuggestPrompt instance.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        /*public string */$label,
        /*public */Closure $options,
        /*public string */$placeholder = '',
        /*public int */$scroll = 5,
        /*public *//*?*/Closure $validate = null
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->scroll = backport_type_check('int', $scroll);
        $this->options = $options;
        $this->validate = $validate;

        $this->trackTypedValue(/*$default = */'', /*submit: */false);

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::SHIFT_TAB: return $this->highlightPrevious();

            case Key::DOWN:
            case Key::TAB: return $this->highlightNext();

            case Key::ENTER: return $this->highlighted !== null
                ? $this->submit()
                : $this->search();

            case Key::LEFT:
            case Key::RIGHT: return $this->highlighted = null;

            default: return $this->search();
        } });
    }

    protected function search()/*: void*/
    {
        $this->state = 'searching';
        $this->highlighted = null;
        $this->render();
        $this->matches = null;
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
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious()/*: void*/
    {
        if ($this->matches === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = count($this->matches) - 1;
        } elseif ($this->highlighted === 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $this->highlighted - 1;
        }
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext()/*: void*/
    {
        if ($this->matches === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = 0;
        } else {
            $this->highlighted = $this->highlighted === count($this->matches) - 1 ? null : $this->highlighted + 1;
        }
    }

    public function searchValue()/*: string*/
    {
        return $this->typedValue;
    }

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

    /**
     * Truncate a value with an ellipsis if it exceeds the given length.
     */
    protected function truncate(/*string */$value, /*int */$length)/*: string*/
    {
        $length = backport_type_check('int', $length);

        $value = backport_type_check('string', $value);

        if ($length <= 0) {
            throw new InvalidArgumentException("Length [{$length}] must be greater than zero.");
        }

        return mb_strlen($value) <= $length ? $value : (mb_substr($value, 0, $length - 1).'…');
    }
}
