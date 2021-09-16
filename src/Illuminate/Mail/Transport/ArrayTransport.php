<?php

namespace Illuminate\Mail\Transport;

use Illuminate\Mail\SentMessage;
use Illuminate\Support\Collection;
use Swift_Mime_Message as RawMessage;

class ArrayTransport extends Transport
{
    /**
     * The collection of Symfony Messages.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $messages;

    /**
     * Create a new array transport instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->messages = new Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, /*Envelope */&$failedRecipients = null)/*: ?SentMessage*/
    {
        $this->beforeSendPerformed($message);

        $this->messages[] = $sentMessage = new SentMessage($message);

        $this->numberOfRecipients($message);

        return $sentMessage;
    }

    /**
     * Retrieve the collection of messages.
     *
     * @return \Illuminate\Support\Collection
     */
    public function messages()
    {
        return $this->messages;
    }

    /**
     * Clear all of the messages from the local collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function flush()
    {
        return $this->messages = new Collection;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString()/*: string*/
    {
        return 'array';
    }
}
