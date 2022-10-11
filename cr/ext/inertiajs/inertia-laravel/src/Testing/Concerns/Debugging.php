<?php

namespace Inertia\Testing\Concerns;

trait Debugging
{
    public function dump(/*string */$prop = null)/*: self*/
    {
        $prop = backport_type_check('?string', $prop);

        dump($this->prop($prop));

        return $this;
    }

    public function dd(/*string */$prop = null)/*: void*/
    {
        $prop = backport_type_check('?string', $prop);

        dd($this->prop($prop));
    }

    abstract protected function prop(string $key = null);
}
