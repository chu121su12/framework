<?php

/*declare(strict_types=1);*/

namespace Termwind\Actions;

use Termwind\Exceptions\StyleNotFound;
use Termwind\Repositories\Styles as StyleRepository;
use Termwind\Terminal;
use Termwind\ValueObjects\Styles;

/**
 * @internal
 */
final class StyleToMethod
{
    /**
     * Finds if there is any media query on the style class.
     */
    /*private */const MEDIA_QUERIES_REGEX = "/^(sm|md|lg|xl|2xl)\:(.*)/";

    /**
     * Defines the Media Query Breakpoints.
     */
    /*public */const MEDIA_QUERY_BREAKPOINTS = [
        'sm' => 64,
        'md' => 76,
        'lg' => 102,
        'xl' => 128,
        '2xl' => 153,
    ];

    private /*Styles */$styles;

    private /*string */$style;

    /**
     * Creates a new action instance.
     */
    public function __construct(
        /*private */Styles $styles,
        /*private string */$style
    ) {
        $this->styles = $styles;
        $this->style = $style;
    }

    /**
     * Applies multiple styles to the given styles.
     */
    public static function multiple(Styles $styles, /*string */$stylesString)/*: Styles*/
    {
        $stylesString = backport_type_check('string', $stylesString);

        $stylesString = self::sortStyles(array_merge(
            $styles->defaultStyles(),
            array_filter((array) preg_split('/(?![^\[]*\])\s/', $stylesString))
        ));

        foreach ($stylesString as $style) {
            $styles = (new self($styles, $style))->__invoke();
        }

        return $styles;
    }

    /**
     * Converts the given style to a method name.
     *
     * @return Styles
     */
    public function __invoke(/*string|int */...$arguments)/*: Styles*/
    {
        $arguments = backport_array_type_check('string|int', $arguments);

        if (StyleRepository::has($this->style)) {
            $styled = StyleRepository::get($this->style);
            return $styled($this->styles, ...$arguments);
        }

        $method = $this->applyMediaQuery($this->style);

        if ($method === '') {
            return $this->styles;
        }

        $method = array_filter(
            (array) preg_split('/(?![^\[]*\])-/', $method),
            function ($item) { return $item !== false; }
        );

        $method = array_slice($method, 0, count($method) - count($arguments));

        $methodName = implode(' ', $method);
        $methodName = ucwords($methodName);
        $methodName = lcfirst($methodName);
        $methodName = str_replace(' ', '', $methodName);

        if ($methodName === '') {
            throw StyleNotFound::fromStyle($this->style);
        }

        if (! method_exists($this->styles, $methodName)) {
            $argument = array_pop($method);

            $arguments[] = backport_is_numeric($argument) ? (int) $argument : (string) $argument;

            return $this->__invoke(...$arguments);
        }

        return $this->styles
            ->setStyle($this->style)
            ->$methodName(...array_reverse($arguments));
    }

    /**
     * Sorts all the styles based on the correct render order.
     *
     * @param  string[]  $styles
     * @return string[]
     */
    private static function sortStyles(array $styles)/*: array*/
    {
        $keys = array_keys(self::MEDIA_QUERY_BREAKPOINTS);

        usort($styles, function ($a, $b) use ($keys) {
            $existsA = (bool) preg_match(self::MEDIA_QUERIES_REGEX, $a, $matchesA);
            $existsB = (bool) preg_match(self::MEDIA_QUERIES_REGEX, $b, $matchesB);

            if ($existsA && ! $existsB) {
                return 1;
            }

            if ($existsA && array_search($matchesA[1], $keys, true) > array_search($matchesB[1], $keys, true)) {
                return 1;
            }

            return -1;
        });

        return $styles;
    }

    /**
     * Applies the media query if exists.
     */
    private function applyMediaQuery(/*string */$method)/*: string*/
    {
        $method = backport_type_check('string', $method);

        preg_match(self::MEDIA_QUERIES_REGEX, $method, $matches);

        if (count(isset($matches) ? $matches : []) < 1) {
            return $method;
        }

        list(, $size, $method) = $matches;

        if ((new Terminal)->width() >= self::MEDIA_QUERY_BREAKPOINTS[$size]) {
            return $method;
        }

        return '';
    }
}
