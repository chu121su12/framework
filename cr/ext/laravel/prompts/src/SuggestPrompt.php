<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class SuggestPrompt extends Prompt
{
    use Concerns\Scrolling;
    use Concerns\Truncation;
    use Concerns\TypedValue;

    /**
     * The options for the suggest prompt.
     *
     * @var array<string>|Closure(string): array<string>
     */
    public /*array|Closure */$options;

    /**
     * The cache of matches.
     *
     * @var array<string>|null
     */
    protected /*?array */$matches = null;

    public /*string */$label;
    public /*string */$placeholder;
    public /*string */$default;
    // public /*int */$scroll;
    public /*bool|string */$required;
    public /*?Closure */$validate;
    public /*string */$hint;

    /**
     * Create a new SuggestPrompt instance.
     *
     * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
     */
    public function __construct(
        /*public string */$label,
        /*array|Collection|Closure */$options,
        /*public string */$placeholder = '',
        /*public string */$default = '',
        /*public int */$scroll = 5,
        /*public bool|string */$required = false,
        /*public mixed*/$validate = null,
        /*public string */$hint

    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->default = backport_type_check('string', $default);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = backport_type_check('mixed', $validate);
        $this->hint = backport_type_check('string', $hint);
        $options = backport_type_check(['array', Collection::class, Closure::class], $options);

        $this->options = $options instanceof Collection ? $options->all() : $options;

        $this->initializeScrolling(null);

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::SHIFT_TAB:
            case Key::CTRL_P: return $this->highlightPrevious(count($this->matches()), true);

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::TAB:
            case Key::CTRL_N: return $this->highlightNext(count($this->matches()), true);

            case Key::oneOf([Key::HOME, Key::CTRL_A], $key): return $this->highlighted !== null ? $this->highlight(0) : null;
            case Key::oneOf([Key::END, Key::CTRL_E], $key): return $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null;

            case Key::ENTER: return $this->selectHighlighted();

            case Key::oneOf([Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_B, Key::CTRL_F], $key): return $this->highlighted = null;

            default: return value(function () {
                $this->highlighted = null;
                $this->matches = null;
                $this->firstVisible = 0;
            });
        } });

        $this->trackTypedValue(
            $default,
            /*submit: */false,
            /*ignore: */function ($key) {
                return Key::oneOf([Key::HOME, Key::END, Key::CTRL_A, Key::CTRL_E], $key) && $this->highlighted !== null;
            }
        );
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(/*int */$maxWidth)/*: string*/
    {
        $maxWidth = backport_type_check('int', $maxWidth);

        if ($this->highlighted !== null) {
            return $this->value() === ''
                ? $this->dim($this->truncate($this->placeholder, $maxWidth))
                : $this->truncate($this->value(), $maxWidth);
        }

        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->value(), $this->cursorPosition, $maxWidth);
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

        if ($this->options instanceof Closure) {
            return $this->matches = array_values(call_user_func($this->options, $this->value()));
        }

        return $this->matches = array_values(array_filter($this->options, function ($option) {
            return str_starts_with(strtolower($option), strtolower($this->value()));
        }));
    }

    /**
     * The current visible matches.
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
     * Select the highlighted entry.
     */
    protected function selectHighlighted()/*: void*/
    {
        if ($this->highlighted === null) {
            return;
        }

        $this->typedValue = $this->matches()[$this->highlighted];
    }
}
