<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SelectPrompt extends Prompt
{
    use Concerns\ReducesScrollingToFitTerminal;

    /**
     * The index of the highlighted option.
     */
    public /*int */$highlighted = 0;

    /**
     * The index of the first visible option.
     */
    public /*int */$firstVisible = 0;

    /**
     * The options for the select prompt.
     *
     * @var array<int|string, string>
     */
    public /*array */$options;

    public /*string */$label;
    public /*int|string|null */$default;
    public /*int */$scroll;
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
        /*public *//*?*/Closure $validate = null,
        /*public string */$hint = '',
        /*public bool|string */$required = ''
    ) {
        $this->label = backport_type_check('string', $label);
        $this->default = backport_type_check('int|string|null', $default);
        $this->scroll = backport_type_check('int', $scroll);
        $this->hint = backport_type_check('string', $hint);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = $validate;
        $options = backport_type_check(['array', Collection::class], $options);

        if ($this->required === false) {
            throw new InvalidArgumentException('Argument [required] must be true or a string.');
        }

        $this->options = $options instanceof Collection ? $options->all() : $options;

        $this->reduceScrollingToFitTerminal();

        if ($this->default) {
            if (array_is_list($this->options)) {
                $this->highlighted = array_search($this->default, $this->options) ?: 0;
            } else {
                $this->highlighted = array_search($this->default, array_keys($this->options)) ?: 0;
            }

            // If the default is not visible, scroll and center it.
            // If it's near the end of the list, we just scroll to the end.
            if ($this->highlighted >= $this->scroll) {
                $optionsLeft = count($this->options) - $this->highlighted - 1;
                $halfScroll = (int) floor($this->scroll / 2);
                $endOffset = max(0, $halfScroll - $optionsLeft);

                // If the scroll is even, we need to subtract one more
                // in order to take the highlighted option into account.
                // Since when the scroll is odd the halfScroll is floored,
                // we don't need to do anything.
                if ($this->scroll % 2 === 0) {
                    $endOffset--;
                }

                $this->firstVisible = $this->highlighted - $halfScroll - $endOffset;
            }
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
            case 'h': return $this->highlightPrevious();

            case Key::DOWN:
            case Key::DOWN_ARROW:
            case Key::RIGHT:
            case Key::RIGHT_ARROW:
            case Key::TAB:
            case Key::CTRL_N:
            case Key::CTRL_F:
            case 'j':
            case 'l': return $this->highlightNext();

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
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious()/*: void*/
    {
        $this->highlighted = $this->highlighted === 0 ? count($this->options) - 1 : $this->highlighted - 1;

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible--;
        } elseif ($this->highlighted === count($this->options) - 1) {
            $this->firstVisible = count($this->options) - min($this->scroll, count($this->options));
        }
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext()/*: void*/
    {
        $this->highlighted = $this->highlighted === count($this->options) - 1 ? 0 : $this->highlighted + 1;

        if ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible++;
        } elseif ($this->highlighted === 0) {
            $this->firstVisible = 0;
        }
    }
}
