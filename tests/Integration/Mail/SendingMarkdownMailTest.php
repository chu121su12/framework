<?php

namespace Illuminate\Tests\Integration\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;

class SendingMarkdownMailTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.driver', 'array');

        View::addNamespace('mail', __DIR__.'/Fixtures');
        View::addLocation(__DIR__.'/Fixtures');
    }

    public function testMailIsSent()
    {
        $mailable = new BasicMailable();

        $mailable
            ->assertHasSubject('My basic title')
            ->assertSeeInText('My basic content')
            ->assertSeeInHtml('My basic content');
    }

    public function testMailMayHaveSpecificTextView()
    {
        $mailable = new BasicMailableWithTextView();

        $mailable
            ->assertHasSubject('My basic title')
            ->assertSeeInHtml('My basic content')
            ->assertSeeInText('My basic text view')
            ->assertDontSeeInText('My basic content');
    }

    public function testEmbed()
    {
        $this->markTestSkipped('Needs mailer to be ported to symfony/mailer');

        Mail::to('test@mail.com')->send($mailable = new EmbedMailable());

        $mailable->assertSeeInHtml('Embed content: cid:');
        $mailable->assertSeeInText('Embed content: ');
        $mailable->assertDontSeeInText('Embed content: cid:');

        $email = app('mailer')->getSymfonyTransport()->messages()[0]->getOriginalMessage()->toString();

        $cid = explode(' cid:', str($email)->explode("\r\n")
            ->filter(function ($line) { return str_contains($line, 'Embed content: cid:'); })
            ->first())[1];

        $expected = <<<EOT
Content-Type: application/x-php; name=$cid\r
Content-Transfer-Encoding: base64\r
Content-Disposition: inline; name=$cid; filename=$cid\r
EOT;
        $this->assertStringContainsString($expected, $email);
    }

    public function testEmbedData()
    {
        $this->markTestSkipped('Needs mailer to be ported to symfony/mailer');

        Mail::to('test@mail.com')->send($mailable = new EmbedDataMailable());

        $mailable->assertSeeInHtml('Embed data content: cid:foo.jpg');
        $mailable->assertSeeInText('Embed data content: ');
        $mailable->assertDontSeeInText('Embed data content: cid:foo.jpg');

        $email = app('mailer')->getSymfonyTransport()->messages()[0]->getOriginalMessage()->toString();

        $expected = <<<EOT
Content-Type: image/png; name=foo.jpg\r
Content-Transfer-Encoding: base64\r
Content-Disposition: inline; name=foo.jpg; filename=foo.jpg\r
EOT;

        $this->assertStringContainsString($expected, $email);
    }

    public function testMessageAsPublicPropertyMayBeDefinedAsViewData()
    {
        $this->markTestSkipped('Needs mailer to be ported to symfony/mailer');

        Mail::to('test@mail.com')->send($mailable = new MessageAsPublicPropertyMailable());

        $mailable
            ->assertSeeInText('My message is: My message.')
            ->assertSeeInHtml('My message is: My message.');

        $email = app('mailer')->getSymfonyTransport()->messages()[0]->getOriginalMessage()->toString();

        $this->assertStringContainsString('My message is: My message.', $email);
    }

    public function testMessageAsWithNamedParameterMayBeDefinedAsViewData()
    {
        $this->markTestSkipped('Needs mailer to be ported to symfony/mailer');

        Mail::to('test@mail.com')->send($mailable = new MessageAsWithNamedParameterMailable());

        $mailable
            ->assertSeeInText('My message is: My message.')
            ->assertSeeInHtml('My message is: My message.');

        $email = app('mailer')->getSymfonyTransport()->messages()[0]->getOriginalMessage()->toString();

        $this->assertStringContainsString('My message is: My message.', $email);
    }

    public function testTheme()
    {
        Mail::to('test@mail.com')->send(new BasicMailable());
        $this->assertSame('default', app(Markdown::class)->getTheme());

        Mail::to('test@mail.com')->send(new BasicMailableWithTheme());
        $this->assertSame('taylor', app(Markdown::class)->getTheme());

        Mail::to('test@mail.com')->send(new BasicMailable());
        $this->assertSame('default', app(Markdown::class)->getTheme());
    }
}

class BasicMailable extends Mailable
{
    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'basic'
        );
    }
}

class BasicMailableWithTheme extends Mailable
{
    public $theme = 'taylor';

    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'basic'
        );
    }
}

class BasicMailableWithTextView extends Mailable
{
    public $textView = 'text';

    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'basic'
        );
    }
}

class EmbedMailable extends Mailable
{
    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'embed'
        );
    }
}

class EmbedDataMailable extends Mailable
{
    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'embed-data'
        );
    }
}

class MessageAsPublicPropertyMailable extends Mailable
{
    public $message = 'My message';

    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'message'
        );
    }
}

class MessageAsWithNamedParameterMailable extends Mailable
{
    public function envelope()
    {
        return new Envelope(
            /*Address|string */$from = null,
            $to = [],
            $cc = [],
            $bcc = [],
            $replyTo = [],
            /*subject: */'My basic title'
        );
    }

    public function content()
    {
        return new Content(
            /*string */$view = null,
            /*string */$html = null,
            /*string */$text = null,
            /*markdown: */'basic',
            /*with: */[
                'message' => 'My message',
            ]
        );
    }
}
