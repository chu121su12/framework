<?php

namespace Illuminate\Mail\Transport;

use Illuminate\Mail\SentMessage;
use Psr\Log\LoggerInterface;
use Swift_Mime_Message as RawMessage;
use Swift_Mime_SimpleMimeEntity;

class LogTransport extends Transport
{
    /**
     * The Logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new log transport instance.
     *
     * @param  \Psr\Log\LoggerInterface  $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, /*Envelope */&$failedRecipients = null)/*: ?SentMessage*/
    {
        $this->beforeSendPerformed($message);

        $this->logger->debug($this->getMimeEntityString($message));

        $this->sendPerformed($message);

        $sentMessage = new SentMessage($message);

        $this->numberOfRecipients($message);

        return $sentMessage;
    }

    /**
     * Get the logger for the LogTransport instance.
     *
     * @return \Psr\Log\LoggerInterface

     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString()/*: string*/
    {
        return 'log';
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     *
     * @param  \Swift_Mime_SimpleMimeEntity  $entity
     * @return string
     */
    protected function getMimeEntityString(Swift_Mime_SimpleMimeEntity $entity)
    {
        $string = (string) $entity->getHeaders().PHP_EOL.$entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= PHP_EOL.PHP_EOL.$this->getMimeEntityString($children);
        }

        return $string;
    }
}
