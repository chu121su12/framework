<?php

namespace Illuminate\Testing\Fluent\Concerns;

trait Debugging
{
    /**
     * Dumps the given props.
     *
     * @param  string|null  $prop
     * @return $this
     */
    public function dump(/*string */$prop = null)/*: self*/
    {
        $prop = backport_type_check('?string', $prop);

        dump($this->prop($prop));

        return $this;
    }

    /**
     * Dumps the given props and exits.
     *
     * @param  string|null  $prop
     * @return never
     */
    public function dd(/*string */$prop = null)/*: void*/
    {
        $prop = backport_type_check('?string', $prop);

        dd($this->prop($prop));
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
     *
     * @param  string|null  $key
     * @return mixed
     */
    abstract protected function prop(/*string */$key = null);
}
