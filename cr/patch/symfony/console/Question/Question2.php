<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Question;

class Question2 extends Question
{
	private $autocompleterCallback;	

    public function setHidden($hidden)
    {
        if ($this->autocompleterCallback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->hidden = (bool) $hidden;

        return $this;
    }

    public function getAutocompleterValues()
    {
        $callback = $this->getAutocompleterCallback();

        return $callback ? $callback('') : null;
    }

    public function setAutocompleterValues($values)
    {
        if (\is_array($values)) {
            $values = $this->isAssoc($values) ? array_merge(array_keys($values), array_values($values)) : array_values($values);

            $callback = static function () use ($values) {
                return $values;
            };
        } elseif ($values instanceof \Traversable) {
            $valueCache = null;
            $callback = static function () use ($values, &$valueCache) {
                return isset($valueCache) ? $valueCache : ($valueCache = iterator_to_array($values, false));
            };
        } elseif (null === $values) {
            $callback = null;
        } else {
            throw new InvalidArgumentException('Autocompleter values can be either an array, "null" or a "Traversable" object.');
        }

        return $this->setAutocompleterCallback($callback);
    }

    public function getAutocompleterCallback()/*: ?callable*/
    {
        return $this->autocompleterCallback;
    }

    public function setAutocompleterCallback(callable $callback = null)/*: self*/
    {
        if ($this->hidden && null !== $callback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->autocompleterCallback = $callback;

        return $this;
    }
}
