<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SuggestPrompt extends Prompt
{
    use Concerns\TypedValue;

    /**
     * The index of the highlighted option.
     */
    public /*?int */$highlighted = null;

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
        /*public *//*?*/Closure $validate = null
    ) {
        $this->label = backport_type_check('string', $label);
        $this->placeholder = backport_type_check('string', $placeholder);
        $this->default = backport_type_check('string', $default);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = $validate;
        $options = backport_type_check(['array', Collection::class, Closure::class], $options);

        $this->options = $options instanceof Collection ? $options->all() : $options;

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::SHIFT_TAB: return $this->highlightPrevious();

            case Key::DOWN:
            case Key::TAB: return $this->highlightNext();

            case Key::ENTER: return $this->selectHighlighted();

            case Key::LEFT:
            case Key::RIGHT: return $this->highlighted = null;

            default: return value(function () {
                $this->highlighted = null;
                $this->matches = null;
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
