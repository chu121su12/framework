<?php

/*declare(strict_types=1);*/

namespace Termwind\Repositories;

use Closure;
use Termwind\ValueObjects\Style;
use Termwind\ValueObjects\Styles as StylesValueObject;

/**
 * @internal
 */
final class Styles
{
    /**
     * @var array<string, Style>
     */
    private static /*array */$storage = [];

    /**
     * Creates a new style from the given arguments.
     *
     * @param  (Closure(StylesValueObject $element, string|int ...$arguments): StylesValueObject)|null  $callback
     * @return Style
     */
    public static function create(/*string */$name, Closure $callback = null)/*: Style*/
    {
        $name = backport_type_check('string', $name);

        self::$storage[$name] = $style = new Style(
            isset($callback) ? $callback : static function (StylesValueObject $styles) { return $styles; }
        );

        return $style;
    }

    /**
     * Removes all existing styles.
     */
    public static function flush()/*: void*/
    {
        self::$storage = [];
    }

    /**
     * Checks a style with the given name exists.
     */
    public static function has(/*string */$name)/*: bool*/
    {
        $name = backport_type_check('string', $name);

        return array_key_exists($name, self::$storage);
    }

    /**
     * Gets the style with the given name.
     */
    public static function get(/*string */$name)/*: Style*/
    {
        $name = backport_type_check('string', $name);

        return self::$storage[$name];
    }
}
