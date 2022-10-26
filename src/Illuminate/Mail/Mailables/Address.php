<?php

namespace Illuminate\Mail\Mailables;

class Address
{
    /**
     * The recipient's email address.
     *
     * @var string
     */
    public $address;

    /**
     * The recipient's name.
     *
     * @var string|null
     */
    public $name;

    /**
     * Create a new address instance.
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return void
     */
    public function __construct(/*string */$address, /*string */$name = null)
    {
        $address = backport_type_check('string', $address);

        $name = backport_type_check('string', $name);

        $this->address = $address;
        $this->name = $name;
    }
}
