<?php

namespace Inertia\Testing\Concerns;

trait Debugging
{
    public function dump(/*string */$prop = null)/*: self*/
    {
        $prop = cast_to_string($prop, null);

        dump($this->prop($prop));

        return $this;
    }

    public function dd(/*string */$prop = null)/*: void*/
    {
        $prop = cast_to_string($prop, null);

        dd($this->prop($prop));
    }

    abstract protected function prop(string $key = null);
}
