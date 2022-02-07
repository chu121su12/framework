<?php

namespace Illuminate\Mail;

use Illuminate\Support\Traits\ForwardsCalls;
use Swift_Attachment;
use Swift_Image;
use Swift_Message as Email;

/**
 * @mixin \Swift_Message
 */
class Message
{
    use ForwardsCalls;

    /**
     * The Symfony Email instance.
     *
     * @var \Swift_Message
     */
    protected $message;

    /**
     * CIDs of files embedded in the message.
     *
     * @var array
     */
    protected $embeddedFiles = [];

    /**
     * Create a new message instance.
     *
     * @param  \Swift_Message  $message
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Add a "from" address to the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function from($address, $name = null)
    {
        is_array($address)
            ? $this->message->setFrom(...$address)
            : $this->message->setFrom($address, (string) $name);

        return $this;
    }

    /**
     * Set the "sender" of the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function sender($address, $name = null)
    {
        is_array($address)
            ? $this->message->setSender(...$address)
            : $this->message->setSender($address, (string) $name);

        return $this;
    }

    /**
     * Set the "return path" of the message.
     *
     * @param  string  $address
     * @return $this
     */
    public function returnPath($address)
    {
        $this->message->setReturnPath($address);

        return $this;
    }

    /**
     * Add a recipient to the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function to($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->setTo(...$address)
                : $this->message->setTo($address, (string) $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'To');
    }

    /**
     * Add a carbon copy to the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function cc($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->setCc(...$address)
                : $this->message->setCc($address, (string) $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Cc');
    }

    /**
     * Remove all carbon copy addresses from the message.
     *
     * @return $this
     */
    public function forgetCc()
    {
        if ($header = $this->message->getHeaders()->get('Cc')) {
            $header->setAddresses([]);
        }

        return $this;
    }

    /**
     * Add a blind carbon copy to the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function bcc($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->setBcc(...$address)
                : $this->message->setBcc($address, (string) $name);

            return $this;
        }

        return $this->addAddresses($address, $name, 'Bcc');
    }

    /**
     * Remove all of the blind carbon copy addresses from the message.
     *
     * @return $this
     */
    public function forgetBcc()
    {
        if ($header = $this->message->getHeaders()->get('Bcc')) {
            $header->setAddresses([]);
        }

        return $this;
    }

    /**
     * Add a "reply to" address to the message.
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function replyTo($address, $name = null)
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }

    /**
     * Add a recipient to the message.
     *
     * @param  string|array  $address
     * @param  string  $name
     * @param  string  $type
     * @return $this
     */
    protected function addAddresses($address, $name, $type)
    {
        if (is_array($address)) {
            $type = ucfirst($type);

            $addresses = collect($address)->each(function (/*string|array */$address) use ($type) {
                if (is_array($address)) {
                    $this->message->{"set{$type}"}(isset($address['email']) ? $address['email'] : $address['address'], isset($address['name']) ? $address['name'] : null);
                } else {
                    $this->message->{"set{$type}"}($address);
                }
            });
        } else {
            $this->message->{"add{$type}"}($address, (string) $name);
        }

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
     *
     * @param  int  $level
     * @return $this
     */
    public function priority($level)
    {
        $this->message->setPriority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param  string  $file
     * @param  array  $options
     * @return $this
     */
    public function attach($file, array $options = [])
    {
        $this->prepAttachment($this->createAttachmentFromPath($file), $options);

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function attachData($data, $name, array $options = [])
    {
        $this->prepAttachment($this->createAttachmentFromData($data, $name), $options);

        return $this;
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param  string  $file
     * @return string
     */
    public function embed($file)
    {
        if (isset($this->embeddedFiles[$file])) {
            return $this->embeddedFiles[$file];
        }

        return $this->embeddedFiles[$file] = $this->message->embed(
            Swift_Image::fromPath($file)
        );
    }

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  string|null  $contentType
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $image = new Swift_Image($data, $name, $contentType);

        return $this->message->embed($image);
    }

    /**
     * Get the underlying Symfony Email instance.
     *
     * @return \Swift_Message
     */
    public function getSymfonyMessage()
    {
        return $this->message;
    }

    /**
     * Dynamically pass missing methods to the Symfony instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardDecoratedCallTo($this->message, $method, $parameters);
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @param  string  $file
     * @return \Swift_Mime_Attachment
     */
    protected function createAttachmentFromPath($file)
    {
        return Swift_Attachment::fromPath($file);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param  string  $data
     * @param  string  $name
     * @return \Swift_Attachment
     */
    protected function createAttachmentFromData($data, $name)
    {
        return new Swift_Attachment($data, $name);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param  \Swift_Attachment  $attachment
     * @param  array  $options
     * @return $this
     */
    protected function prepAttachment($attachment, $options = [])
    {
        // First we will check for a MIME type on the message, which instructs the
        // mail client on what type of attachment the file is so that it may be
        // downloaded correctly by the user. The MIME option is not required.
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        // If an alternative name was given as an option, we will set that on this
        // attachment so that it will be downloaded with the desired names from
        // the developer, otherwise the default file names will get assigned.
        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $this->message->attach($attachment);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function html($content)
    {
        $this->setBody($content, 'text/html');
    }

    /**
     * {@inheritdoc}
     */
    public function text($content, $addPart = false)
    {
        $method = $addPart ? 'addPart' : 'setBody';

        $this->$method($content, 'text/plain');
    }

    /**
     * {@inheritdoc}
     */
    public function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->setBody($this->renderView($view, $data) ?: ' ', 'text/html');
        }

        if (isset($plain)) {
            $method = isset($view) ? 'addPart' : 'setBody';

            $message->$method($this->renderView($plain, $data) ?: ' ', 'text/plain');
        }

        if (isset($raw)) {
            $method = (isset($view) || isset($plain)) ? 'addPart' : 'setBody';

            $message->$method($raw, 'text/plain');
        }
    }
}
