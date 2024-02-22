<?php

namespace Illuminate\Tests\Mail;

use Illuminate\Mail\Attachment;
use Illuminate\Mail\Message;
use Illuminate\Mail\Transport\LogTransport;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orchestra\Testbench\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Mime\Email;

class MailLogTransportTest_getLoggedEmailMessage_class extends NullLogger
        {
            public /*string */$loggedValue = '';

            public function log($level, /*string|\Stringable */$message, array $context = [])/*: void*/
            {
                $message = backport_type_check('string|\Stringable', $message);

                $this->loggedValue = (string) $message;
            }
        }

class MailLogTransportTest extends TestCase
{
    public function testGetLogTransportWithConfiguredChannel()
    {
        $this->app['config']->set('mail.driver', 'log');

        $this->app['config']->set('mail.log_channel', 'mail');

        $this->app['config']->set('logging.channels.mail', [
            'driver' => 'single',
            'path' => 'mail.log',
        ]);

        $transport = app('mailer')->getSymfonyTransport();
        $this->assertInstanceOf(LogTransport::class, $transport);

        $logger = $transport->logger();
        $this->assertInstanceOf(LoggerInterface::class, $logger);

        $this->assertInstanceOf(Logger::class, $monolog = $logger->getLogger());
        $this->assertCount(1, $handlers = $monolog->getHandlers());
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);
    }

    public function testItDecodesTheMessageBeforeLogging()
    {
        $htmlBody = <<<'BODY'
Hi,

<a href="https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299">Click here to reset your password</a>.

All the best,

Burt & Irving
BODY;

        $message = (new Message(new Email))
            ->from('noreply@example.com', 'no-reply')
            ->to('taylor@example.com', 'Taylor')
            ->html($htmlBody)
            ->text('A text part');

        $actualLoggedValue = $this->getLoggedEmailMessage($message);

        $this->assertStringNotContainsString("=\r\n", $actualLoggedValue);
        $this->assertStringContainsString('href=', $actualLoggedValue);
        $this->assertStringContainsString('Burt & Irving', $actualLoggedValue);
        $this->assertStringContainsString('https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299', $actualLoggedValue);
    }

    public function testItOnlyDecodesQuotedPrintablePartsOfTheMessageBeforeLogging()
    {
        $htmlBody = <<<'BODY'
            Hi,

            <a href="https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299">Click here to reset your password</a>.

            All the best,

            Burt & Irving
BODY;

        $message = (new Message(new Email))
            ->from('noreply@example.com', 'no-reply')
            ->to('taylor@example.com', 'Taylor')
            ->html($htmlBody)
            ->text('A text part')
            ->attach(Attachment::fromData(function () { return 'My attachment'; }, 'attachment.txt'));

        $actualLoggedValue = $this->getLoggedEmailMessage($message);

        $this->assertStringContainsString('href=', $actualLoggedValue);
        $this->assertStringContainsString('Burt & Irving', $actualLoggedValue);
        $this->assertStringContainsString('https://example.com/reset-password=5e113c71a4c210aff04b3fa66f1b1299', $actualLoggedValue);
        $this->assertStringContainsString('name=attachment.txt', $actualLoggedValue);
        $this->assertStringContainsString('filename=attachment.txt', $actualLoggedValue);
    }

    public function testGetLogTransportWithPsrLogger()
    {
        $this->app['config']->set('mail.driver', 'log');

        $logger = $this->app->instance('log', new NullLogger);

        $transportLogger = app('mailer')->getSymfonyTransport()->logger();

        $this->assertEquals($logger, $transportLogger);
    }

    private function getLoggedEmailMessage(Message $message)/*: string*/
    {
        $logger = new MailLogTransportTest_getLoggedEmailMessage_class;

        (new LogTransport($logger))->send(
            $message->getSymfonyMessage()
        );

        return $logger->loggedValue;
    }
}
