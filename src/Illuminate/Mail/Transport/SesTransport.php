<?php

namespace Illuminate\Mail\Transport;

use Aws\SesV2\SesV2Client;
use Illuminate\Mail\SentMessage;
use Swift_Mime_SimpleMessage as SwiftMimeSimpleMessage;
use Swift_Mime_Message as Swift_Mime_SimpleMessage;

class SesTransport extends Transport
{
    /**
     * The Amazon SES instance.
     *
     * @var \Aws\SesV2\SesV2Client
     */
    protected $ses;

    /**
     * The Amazon SES transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SES transport instance.
     *
     * @param  \Aws\SesV2\SesV2Client  $ses
     * @param  array  $options
     * @return void
     */
    public function __construct(SesV2Client $ses, $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $result = $this->ses->sendEmail(
            array_merge(
                $this->options, [
                    'Content' => [
                        'Raw' => ['Data' => $message->toString()],
                    ],
                ]
            )
        );

        $messageId = $result->get('MessageId');

        $message->getHeaders()->addTextHeader('X-Message-ID', $messageId);
        $message->getHeaders()->addTextHeader('X-SES-Message-ID', $messageId);

        $this->sendPerformed($message);

        $sentMessage = new SentMessage($message);

        $this->numberOfRecipients($message);

        return $sentMessage;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message)/*: void*/
    {
        $options = $this->options;

        if ($message->getOriginalMessage() instanceof Message) {
            foreach ($message->getOriginalMessage()->getHeaders()->all() as $header) {
                if ($header instanceof MetadataHeader) {
                    $options['Tags'][] = ['Name' => $header->getKey(), 'Value' => $header->getValue()];
                }
            }
        }

        try {
            $result = $this->ses->sendRawEmail(
                array_merge(
                    $options, [
                        'Source' => $message->getEnvelope()->getSender()->toString(),
                        'Destinations' => collect($message->getEnvelope()->getRecipients())
                                ->map
                                ->toString()
                                ->values()
                                ->all(),
                        'RawMessage' => [
                            'Data' => $message->toString(),
                        ],
                    ]
                )
            );
        } catch (AwsException $e) {
            $awsErrorMessage = $e->getAwsErrorMessage();
            $reason = isset($awsErrorMessage) ? $awsErrorMessage : $e->getMessage();

            throw new Exception(
                sprintf('Request to AWS SES API failed. Reason: %s.', $reason),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }

        $messageId = $result->get('MessageId');

        $message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId);
        $message->getOriginalMessage()->getHeaders()->addHeader('X-SES-Message-ID', $messageId);
    }

    /**
     * Get the Amazon SES client for the SesTransport instance.
     *
     * @return \Aws\SesV2\SesV2Client
     */
    public function ses()
    {
        return $this->ses;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param  array  $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'ses';
    }
}
