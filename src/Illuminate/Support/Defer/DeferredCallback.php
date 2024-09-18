<?php

namespace Illuminate\Support\Defer;

use Illuminate\Support\Str;

class DeferredCallback
{
    public $callback;
    public $name;
    public $always;

    /**
     * Create a new deferred callback instance.
     *
     * @param  callable  $callback
     * @return void
     */
    public function __construct(/*public */$callback, /*public ?string */$name = null, /*public bool */$always = false)
    {
        $this->callback = $callback;
        $this->always = backport_type_check('bool', $always);

        $this->name = isset($name) ? backport_type_check('?string', $name) : (string) Str::uuid();
    }

    /**
     * Specify the name of the deferred callback so it can be cancelled later.
     *
     * @param  string  $name
     * @return $this
     */
    public function name(/*string */$name)/*: self*/
    {
        $name = backport_type_check('string', $name);

        $this->name = $name;

        return $this;
    }

    /**
     * Indicate that the deferred callback should run even on unsuccessful requests and jobs.
     *
     * @param  bool  $always
     * @return $this
     */
    public function always(/*bool */$always = true)/*: self*/
    {
        $always = backport_type_check('bool', $always);

        $this->always = $always;

        return $this;
    }

    /**
     * Invoke the deferred callback.
     *
     * @return void
     */
    public function __invoke()/*: void*/
    {
        call_user_func($this->callback);
    }
}
