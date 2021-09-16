<?php

namespace Illuminate\Mail\Events;

class MessageSending
{
    /**
     * The Symfony Email instance.
     *
     * @var \Swift_Message
     */
    public $message;

    /**
     * The message data.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param  \Swift_Message  $message
     * @param  array  $data
     * @return void
     */
    public function __construct(/*Email */$message, array $data = [])
    {
        $this->data = $data;
        $this->message = $message;
    }
}
