<?php

namespace Illuminate\Mail\Transport;

use Illuminate\Mail\SentMessage;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Stringable;
use Swift_Mime_Message as RawMessage;
use Swift_Mime_SimpleMimeEntity;
use Symfony\Component\Mailer\Envelope;
// use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
// use Symfony\Component\Mime\RawMessage;

// class LogTransport implements Stringable, TransportInterface
class LogTransport extends Transport implements Stringable
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
        $string = Str::of($message->toString());

        if ($string->contains('Content-Type: multipart/')) {
            $boundary = $string
                ->after('boundary=')
                ->before("\r\n")
                ->prepend('--')
                ->append("\r\n");

            $string = $string
                ->explode($boundary)
                ->map(function (...$args) { return $this->decodeQuotedPrintableContent(...$args); })
                ->implode($boundary);
        } elseif ($string->contains('Content-Transfer-Encoding: quoted-printable')) {
            $string = $this->decodeQuotedPrintableContent($string);
        }

        $this->logger->debug((string) $string);

        $this->numberOfRecipients($message);

        return $sentMessage;
    }

    /**
     * Decode the given quoted printable content.
     *
     * @param  string  $part
     * @return string
     */
    protected function decodeQuotedPrintableContent(/*string */$part)
    {
        $part = backport_type_check('string', $part);

        if (! str_contains($part, 'Content-Transfer-Encoding: quoted-printable')) {
            return $part;
        }

        list($headers, $content) = explode("\r\n\r\n", $part, 2);

        return implode("\r\n\r\n", [
            $headers,
            quoted_printable_decode($content),
        ]);
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
