<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class MultiSelectPrompt extends Prompt
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
     * The options for the multi-select prompt.
     *
     * @var array<int|string, string>
     */
    public /*array */$options;

    /**
     * The default values the multi-select prompt.
     *
     * @var array<int|string>
     */
    public /*array */$default;

    /**
     * The selected values.
     *
     * @var array<int|string>
     */
    protected /*array */$values = [];

    public /*string */$label;
    public /*int */$scroll;
    public /*bool|string */$required;
    public /*?Closure */$validate;
    public /*string */$hint;

    /**
     * Create a new MultiSelectPrompt instance.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     * @param  array<int|string>|Collection<int, int|string>  $default
     */
    public function __construct(
        /*public string */$label,
        /*array|Collection */$options,
        /*array|Collection */$default = [],
        /*public int */$scroll = 5,
        /*public bool|string */$required = false,
        /*public *//*?*/Closure $validate = null,
        /*public string */$hint
    ) {
        $this->label = backport_type_check('string', $label);
        $this->scroll = backport_type_check('int', $scroll);
        $this->required = backport_type_check('bool|string', $required);
        $this->validate = $validate;
        $this->hint = backport_type_check('string', $hint);
        $options = backport_type_check(['array', Collection::class], $options);
        $default = backport_type_check(['array', Collection::class], $default);

        $this->options = $options instanceof Collection ? $options->all() : $options;
        $this->default = $default instanceof Collection ? $default->all() : $default;
        $this->values = $this->default;

        $this->reduceScrollingToFitTerminal();

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

            case Key::SPACE: return $this->toggleHighlighted();
            case Key::ENTER: return $this->submit();
            default: return null;
        } });
    }

    /**
     * Get the selected values.
     *
     * @return array<int|string>
     */
    public function value()/*: array*/
    {
        return array_values($this->values);
    }

    /**
     * Get the selected labels.
     *
     * @return array<string>
     */
    public function labels()/*: array*/
    {
        if (array_is_list($this->options)) {
            return array_map(function ($value) { return (string) $value; }, $this->values);
        }

        return array_values(array_intersect_key($this->options, array_flip($this->values)));
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
     * Check whether the value is currently highlighted.
     */
    public function isHighlighted(/*string */$value)/*: bool*/
    {
        $value = backport_type_check('string', $value);

        if (array_is_list($this->options)) {
            return $this->options[$this->highlighted] === $value;
        }

        return array_keys($this->options)[$this->highlighted] === $value;
    }

    /**
     * Check whether the value is currently selected.
     */
    public function isSelected(/*string */$value)/*: bool*/
    {
        $value = backport_type_check('string', $value);

        return in_array($value, $this->values);
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

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted()/*: void*/
    {
        $value = array_is_list($this->options)
            ? $this->options[$this->highlighted]
            : array_keys($this->options)[$this->highlighted];

        if (in_array($value, $this->values)) {
            $this->values = array_filter($this->values, function ($v) use ($value) { return $v !== $value; });
        } else {
            $this->values[] = $value;
        }
    }
}
