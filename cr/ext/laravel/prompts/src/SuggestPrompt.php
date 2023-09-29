<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class SuggestPrompt extends Prompt
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
    public /*int */$scroll;
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
        /*public *//*?*/Closure $validate = null,
        /*public string */$hint

    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->default = backport_type_check('string', $default);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->hint = backport_type_check('string', $hint);
        $this->validate = $validate;
        $options = backport_type_check(['array', Collection::class, Closure::class], $options);

        $this->options = $options instanceof Collection ? $options->all() : $options;

        $this->reduceScrollingToFitTerminal();

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::SHIFT_TAB:
            case Key::CTRL_P: return $this->highlightPrevious();

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::TAB:
            case Key::CTRL_N: return $this->highlightNext();

            case Key::ENTER: return $this->selectHighlighted();

            case Key::LEFT:
            case Key::LEFT_ARROW:
            case Key::RIGHT:
            case Key::RIGHT_ARROW:
            case Key::CTRL_B:
            case Key::CTRL_F:
            case Key::HOME:
            case Key::END:
            case Key::CTRL_A:
            case Key::CTRL_E: return $this->highlighted = null;

            default: return value(function () {
                $this->highlighted = null;
                $this->matches = null;
                $this->firstVisible = 0;
            });
        } });

        $this->trackTypedValue($default);
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
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious()/*: void*/
    {
        if ($this->matches() === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = count($this->matches()) - 1;
        } elseif ($this->highlighted === 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $this->highlighted - 1;
        }

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible--;
        } elseif ($this->highlighted === count($this->matches()) - 1) {
            $this->firstVisible = count($this->matches()) - min($this->scroll, count($this->matches()));
        }
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext()/*: void*/
    {
        if ($this->matches() === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = 0;
        } else {
            $this->highlighted = $this->highlighted === count($this->matches()) - 1 ? null : $this->highlighted + 1;
        }

        if ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible++;
        } elseif ($this->highlighted === 0 || $this->highlighted === null) {
            $this->firstVisible = 0;
        }
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
