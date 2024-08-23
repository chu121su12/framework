<?php

namespace CR\Extra;

class WeakMapIterator implements \Iterator
{
    private $position = 0;

    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    #[\ReturnTypeWillChange]
    public function current()/*: mixed*/
    {
        return $this->content[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key()/*: mixed*/
    {
        return $this->position;
    }

    #[\ReturnTypeWillChange]
    public function next()/*: void*/
    {
        ++$this->position;
    }

    #[\ReturnTypeWillChange]
    public function rewind()/*: void*/
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function valid()/*: bool*/
    {
        return isset($this->content[$this->position]);
    }
}
