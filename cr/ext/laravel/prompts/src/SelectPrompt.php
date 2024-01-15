<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SelectPrompt extends Prompt
{
    use Concerns\Scrolling;

    /**
     * The options for the select prompt.
     *
     * @var array<int|string, string>
     */
    public /*array */$options;

    public /*string */$label;
    public /*int|string|null */$default;
    // public /*int */$scroll;
    public /*?Closure */$validate;
    public /*string */$hint;
    public /*bool|string */$required;

    /**
     * Create a new SelectPrompt instance.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     */
    public function __construct(
        /*public string */$label,
        /*array|Collection */$options,
        /*public int|string|null */$default = null,
        /*public int */$scroll = 5,
        /*public mixed */$validate = null,
        /*public string */$hint = '',
        /*public bool|string */$required = ''
    ) {
        $this->label = backport_type_check('string', $label);
        $this->default = backport_type_check('int|string|null', $default);
        $this->scroll = backport_type_check('int', $scroll);
        $this->validate = backport_type_check('mixed', $validate);
        $this->hint = backport_type_check('string', $hint);
        $this->required = backport_type_check('bool|string', $required);
        $options = backport_type_check(['array', Collection::class], $options);

        if ($this->required === false) {
            throw new InvalidArgumentException('Argument [required] must be true or a string.');
        }

        $this->options = $options instanceof Collection ? $options->all() : $options;

        if ($this->default) {
            if (array_is_list($this->options)) {
                $this->initializeScrolling(array_search($this->default, $this->options) ?: 0);
            } else {
                $this->initializeScrolling(array_search($this->default, array_keys($this->options)) ?: 0);
            }

            $this->scrollToHighlighted(count($this->options));
        } else {
            $this->initializeScrolling(0);
        }

        $this->on('key', function ($key) { switch ($key) {
            case Key::UP:
            case Key::UP_ARROW:
            case Key::LEFT:
            case Key::LEFT_ARROW:
            case Key::SHIFT_TAB:
            case Key::CTRL_P:
            case Key::CTRL_B:
            case 'k':
            case 'h': return $this->highlightPrevious(count($this->options));

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::RIGHT:
            case Key::RIGHT_ARROW:
            case Key::TAB:
            case Key::CTRL_N:
            case Key::CTRL_F:
            case 'j':
            case 'l': return $this->highlightNext(count($this->options));

            case Key::oneOf([Key::HOME, Key::CTRL_A], $key): return $this->highlight(0);
            case Key::oneOf([Key::END, Key::CTRL_E], $key): return $this->highlight(count($this->options) - 1);

            case Key::ENTER: return $this->submit();
            default: return null;
        } });
    }

    /**
     * Get the selected value.
     */
    public function value()/*: int|string|null*/
    {
        if (static::$interactive === false) {
            return $this->default;
        }

        if (array_is_list($this->options)) {
            return isset($this->options[$this->highlighted]) ? $this->options[$this->highlighted] : null;
        } else {
            return array_keys($this->options)[$this->highlighted];
        }
    }

    /**
     * Get the selected label.
     */
    public function label()/*: ?string*/
    {
        if (array_is_list($this->options)) {
            return isset($this->options[$this->highlighted]) ? $this->options[$this->highlighted] : null;
        } else {
            $optionKey = array_keys($this->options)[$this->highlighted];

            return isset($this->options[$optionKey]) ? $this->options[$optionKey] : null;
        }
    }

    /**
     * The currently visible options.
     *
     * @return array<int|string, string>
     */
    public function visible()/*: array*/
    {
        return array_slice(
            $this->options,
            $this->firstVisible,
            $this->scroll,
            /*preserve_keys: */true
        );
    }

    /**
     * Determine whether the given value is invalid when the prompt is required.
     */
    protected function isInvalidWhenRequired(/*mixed */$value)/*: bool*/
    {
        $value = backport_type_check('mixed', $value);

        return $value === null;
    }
}
