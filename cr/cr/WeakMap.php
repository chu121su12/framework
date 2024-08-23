<?php

namespace CR\Extra;

class WeakMap implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $backing = [];

    protected static function hash($object)
    {
        return \is_object($object)
            ? ('(' . \spl_object_id($object) . ')')
            : \serialize($object);
    }

    #[\ReturnTypeWillChange]
    public function count()/*: int*/
    {
        return count($this->backing);

    }

    #[\ReturnTypeWillChange]
    public function getIterator()/*: Iterator*/
    {
        return new WeakMapIterator($this->backing);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(/*object */$object)/*: bool*/
    {
        return isset($this->backing[self::hash($object)]);
    }

    #[\ReturnTypeWillChange]
    public function &offsetGet(/*object */$object)/*: mixed*/
    {
        $hash = self::hash($object);

        if (isset($this->backing[$hash])) {
            return $this->backing[$hash];
        }

        $result = null;

        return $result;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet(/*object */$object, /*mixed */$value)/*: void*/
    {
        $this->backing[self::hash($object)] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset(/*object */$object)/*: void*/
    {
        unset($this->backing[self::hash($object)]);
    }
}
