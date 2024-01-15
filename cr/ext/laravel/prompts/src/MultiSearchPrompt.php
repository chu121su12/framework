<?php

namespace Laravel\Prompts;

use Closure;

class MultiSearchPrompt extends Prompt
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

    /**
     * The selected values.
     *
     * @var array<int|string, string>
     */
    public /*array */$values = [];

    public /*string */$label;
    public /*Closure */$options;
    public /*string */$placeholder;
    // public /*int */$scroll;
    public /*bool|string */$required;
    public /*mixed */$validate;
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
        /*public *//*mixed */$validate = null,
        /*public *//*string */$hint = ''
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->hint = backport_type_check('string', $hint);
        $this->validate = backport_type_check('mixed', $validate);
        $this->options = $options;

        $this->trackTypedValue(
            /*$default = */'',
            /*submit: */false,
            /*ignore: */function ($key) {
                return Key::oneOf([Key::SPACE, Key::HOME, Key::END, Key::CTRL_A, Key::CTRL_E], $key) && $this->highlighted !== null;
            }
        );

        $this->initializeScrolling(null);

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::SHIFT_TAB: return $this->highlightPrevious(count($this->matches), true);

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::TAB: return $this->highlightNext(count($this->matches), true);

            case Key::oneOf([Key::HOME, Key::CTRL_A], $key): return $this->highlighted !== null ? $this->highlight(0) : null;

            case Key::oneOf([Key::END, Key::CTRL_E], $key): return $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null;

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
