<?php

namespace Laravel\Prompts;

use Closure;

class MultiSearchPrompt extends Prompt
{
    use Concerns\ReducesScrollingToFitTerminal;
    use Concerns\Truncation;
    use Concerns\TypedValue;

    /**
     * The index of the highlighted option.
     */
    public /*?int */$highlighted = null;

    /**
     * The index of the first visible option.
     */
    public /*int */$firstVisible = 0;

    /**
     * The cached matches.
     *
     * @var array<int|string, string>|null
     */
    protected /*?array */$matches = null;

    /**
     * The selected values.
     *
     * @var array<int|string, string>
     */
    public /*array */$values = [];

    public /*string */$label;
    public /*Closure */$options;
    public /*string */$placeholder;
    public /*int */$scroll;
    public /*bool|string */$required;
    public /*?Closure */$validate;
    public /*string */$hint;

    /**
     * Create a new MultiSearchPrompt instance.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        /*public *//*string */$label,
        /*public */Closure $options,
        /*public *//*string */$placeholder = '',
        /*public *//*int */$scroll = 5,
        /*public *//*bool|string */$required = false,
        /*public *//*?*/Closure $validate = null,
        /*public *//*string */$hint = ''
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->hint = backport_type_check('string', $hint);
        $this->options = $options;
        $this->validate = $validate;

        $this->trackTypedValue(
            /*$default = */'',
            /*submit: */false,
            /*ignore: */function ($key) { return $key === Key::SPACE && $this->highlighted !== null; }
        );

        $this->reduceScrollingToFitTerminal();

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::SHIFT_TAB: return $this->highlightPrevious();

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::TAB: return $this->highlightNext();

            case Key::SPACE: return $this->highlighted !== null ? $this->toggleHighlighted() : null;
            case Key::ENTER: return $this->submit();

            case Key::LEFT:
            case Key::LEFT_ARROW:
            case Key::RIGHT:
            case Key::RIGHT_ARROW: return $this->highlighted = null;

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

        if (strlen($this->typedValue) === 0) {
            $matches = call_user_func($this->options, $this->typedValue);

            return $this->matches = \array_merge(
                array_diff($this->values, $matches),
                $matches
            );
        }

        return $this->matches = call_user_func($this->options, $this->typedValue);
    }

    /**
     * The currently visible matches
     *
     * @return array<string>
     */
    public function visible()/*: array*/
    {
        return array_slice($this->matches(), $this->firstVisible, $this->scroll, /*preserve_keys: */true);
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

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible--;
        } elseif ($this->highlighted === count($this->matches) - 1) {
            $this->firstVisible = count($this->matches) - min($this->scroll, count($this->matches));
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

        if ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible++;
        } elseif ($this->highlighted === 0 || $this->highlighted === null) {
            $this->firstVisible = 0;
        }
    }

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted()/*: void*/
    {
        if (array_is_list($this->matches)) {
            $label = $this->matches[$this->highlighted];
            $key = $label;
        } else {
            $key = array_keys($this->matches)[$this->highlighted];
            $label = $this->matches[$key];
        }

        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        } else {
            $this->values[$key] = $label;
        }
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
     *
     * @return array<int|string>
     */
    public function value()/*: array*/
    {
        return array_keys($this->values);
    }

    /**
     * Get the selected labels.
     *
     * @return array<string>
     */
    public function labels()/*: array*/
    {
        return array_values($this->values);
    }
}
