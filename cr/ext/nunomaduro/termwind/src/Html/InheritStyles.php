<?php

/*declare(strict_types=1);*/

namespace Termwind\Html;

use Termwind\Components\Element;
use Termwind\Termwind;
use Termwind\ValueObjects\Styles;

/**
 * @internal
 */
final class InheritStyles
{
    /**
     * Applies styles from parent element to child elements.
     *
     * @param  array<int, Element|string>  $elements
     * @return array<int, Element|string>
     */
    public function __invoke(array $elements, Styles $styles)/*: array*/
    {
        $elements = array_values($elements);

        foreach ($elements as &$element) {
            if (is_string($element)) {
                $element = Termwind::raw($element);
            }

            $element->inheritFromStyles($styles);
        }

        $styleProperties = $styles->getProperties();
        $styleProperties = isset($styleProperties['styles']) ? $styleProperties['styles'] : [];

        /** @var Element[] $elements */
        if ((isset($styleProperties['display']) ? $styleProperties['display'] : 'inline') === 'flex') {
            $elements = $this->applyFlex($elements);
        }

        switch (isset($styleProperties['justifyContent']) ? $styleProperties['justifyContent'] : false) {
            case 'between': return $this->applyJustifyBetween($elements);
            case 'evenly': return $this->applyJustifyEvenly($elements);
            case 'around': return $this->applyJustifyAround($elements);
            case 'center': return $this->applyJustifyCenter($elements);
            default: return $elements;
        }
    }

    /**
     * Applies flex-1 to child elements with the class.
     *
     * @param  array<int, Element>  $elements
     * @return array<int, Element>
     */
    private function applyFlex(array $elements)/*: array*/
    {
        list($totalWidth, $parentWidth) = $this->getWidthFromElements($elements);

        $width = max(0, array_reduce($elements, function ($carry, $element) {
            return $carry += $element->hasStyle('flex-1') ? $element->getInnerWidth() : 0;
        }, $parentWidth - $totalWidth));

        $flexed = array_values(array_filter(
            $elements, function ($element) { return $element->hasStyle('flex-1'); }
        ));

        foreach ($flexed as $index => &$element) {
            $elementProperties = $element->getProperties();
            if ($width === 0 && ! (isset($elementProperties['styles']) && isset($elementProperties['styles']['contentRepeat']) ? $elementProperties['styles']['contentRepeat'] : false)) {
                continue;
            }

            $float = $width / count($flexed);
            $elementWidth = floor($float);

            if ($index === count($flexed) - 1) {
                $elementWidth += ($float - floor($float)) * count($flexed);
            }

            $element->addStyle("w-{$elementWidth}");
        }

        return $elements;
    }

    /**
     * Applies the space between the elements.
     *
     * @param  array<int, Element>  $elements
     * @return array<int, Element|string>
     */
    private function applyJustifyBetween(array $elements)/*: array*/
    {
        list($totalWidth, $parentWidth) = $this->getWidthFromElements($elements);
        $space = ($parentWidth - $totalWidth) / (count($elements) - 1);

        if ($space < 1) {
            return $elements;
        }

        $arr = [];

        foreach ($elements as $index => &$element) {
            if ($index !== 0) {
                // Since there is no float pixel, on the last one it should round up...
                $length = $index === count($elements) - 1 ? ceil($space) : floor($space);
                $arr[] = str_repeat(' ', (int) $length);
            }

            $arr[] = $element;
        }

        return $arr;
    }

    /**
     * Applies the space between and around the elements.
     *
     * @param  array<int, Element>  $elements
     * @return array<int, Element|string>
     */
    private function applyJustifyEvenly(array $elements)/*: array*/
    {
        list($totalWidth, $parentWidth) = $this->getWidthFromElements($elements);
        $space = ($parentWidth - $totalWidth) / (count($elements) + 1);

        if ($space < 1) {
            return $elements;
        }

        $arr = [];
        foreach ($elements as &$element) {
            $arr[] = str_repeat(' ', (int) floor($space));
            $arr[] = $element;
        }

        $decimals = ceil(($space - floor($space)) * (count($elements) + 1));
        $arr[] = str_repeat(' ', (int) (floor($space) + $decimals));

        return $arr;
    }

    /**
     * Applies the space around the elements.
     *
     * @param  array<int, Element>  $elements
     * @return array<int, Element|string>
     */
    private function applyJustifyAround(array $elements)/*: array*/
    {
        list($totalWidth, $parentWidth) = $this->getWidthFromElements($elements);
        $space = ($parentWidth - $totalWidth) / count($elements);

        if ($space < 1) {
            return $elements;
        }

        $contentSize = $totalWidth;
        $arr = [];

        foreach ($elements as $index => &$element) {
            if ($index !== 0) {
                $arr[] = str_repeat(' ', (int) ceil($space));
                $contentSize += ceil($space);
            }

            $arr[] = $element;
        }

        array_unshift($arr, str_repeat(' ', (int) floor(($parentWidth - $contentSize) / 2)));
        array_push($arr, str_repeat(' ', (int) ceil(($parentWidth - $contentSize) / 2)));

        return $arr;
    }

    /**
     * Applies the space on before first element and after last element.
     *
     * @param  array<int, Element>  $elements
     * @return array<int, Element|string>
     */
    private function applyJustifyCenter(array $elements)/*: array*/
    {
        list($totalWidth, $parentWidth) = $this->getWidthFromElements($elements);
        $space = $parentWidth - $totalWidth;

        if ($space < 1) {
            return $elements;
        }

        array_unshift($elements, str_repeat(' ', (int) floor($space / 2)));
        array_push($elements, str_repeat(' ', (int) ceil($space / 2)));

        return $elements;
    }

    /**
     * Gets the total width for the elements and their parent width.
     *
     * @param  array<int, Element>  $elements
     * @return int[]
     */
    private function getWidthFromElements(array $elements)
    {
        $totalWidth = (int) array_reduce($elements, function ($carry, $element) {
            return $carry += $element->getLength();
        }, 0);

        $elementProperties = $elements[0]->getProperties();

        $parentWidth = Styles::getParentWidth(isset($elementProperties['parentStyles']) ? $elementProperties['parentStyles'] : []);

        return [$totalWidth, $parentWidth];
    }
}
