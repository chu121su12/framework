<?php

namespace Illuminate\Mail\Events;

use Swift_Attachment;

/**
 * @property \Symfony\Component\Mime\Email $message
 */
class MessageSent
{
    /**
     * The message that was sent.
     *
     * @var \Swift_Message
     */
    public $sent;

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
        $this->sent = $message;
        $this->data = $data;
    }

    /**
     * Get the serializable representation of the object.
     *
     * @return array
     */
    public function __serialize()
    {
        $hasAttachments = collect($this->message->getChildren())
            ->whereInstanceOf(Swift_Attachment::class)
            ->isNotEmpty();

        return $hasAttachments ? [
            'sent' => base64_encode(backport_serialize($this->sent)),
            'data' => base64_encode(backport_serialize($this->data)),
            'hasAttachments' => true,
        ] : [
            'sent' => $this->sent,
            'data' => $this->data,
            'hasAttachments' => false,
        ];
    }

    /**
     * Marshal the object from its serialized data.
     *
     * @param  array  $data
     * @return void
     */
    public function __unserialize(array $data)
    {
        if (isset($data['hasAttachments']) && $data['hasAttachments'] === true) {
            $this->sent = backport_unserialize(base64_decode($data['sent']));
            $this->data = backport_unserialize(base64_decode($data['data']));
        } else {
            $this->sent = $data['sent'];
            $this->data = $data['data'];
        }
    }

    /**
     * Dynamically get the original message.
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key)
    {
        if ($key === 'message') {
            return $this->sent->getOriginalMessage();
        }

        throw new Exception('Unable to access undefined property on '.__CLASS__.': '.$key);
    }
}
