<?php

/*declare(strict_types=1);*/

namespace Termwind\Components;

final class BreakLine extends Element
{
    /**
     * Get the string representation of the element.
     */
    public function toString()/*: string*/
    {
        $properties = $this->styles->getProperties();

        $display = isset($properties['styles']) && isset($properties['styles']['display']) ? $properties['styles']['display'] : 'inline';

        if ($display === 'hidden') {
            return '';
        }

        if ($display === 'block') {
            return parent::toString();
        }

        return parent::toString()."\r";
    }
}
