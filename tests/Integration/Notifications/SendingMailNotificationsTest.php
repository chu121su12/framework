<?php

namespace Illuminate\Tests\Integration\Notifications;

use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Mail\Markdown;
use Illuminate\Mail\Message;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery as m;
use Orchestra\Testbench\TestCase;

/**
 * @group integration
 */
class SendingMailNotificationsTest extends TestCase
{
    public $mailer;
    public $markdown;

    protected function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->mailFactory = m::mock(MailFactory::class);
        $this->mailer = m::mock(Mailer::class);
        $this->mailFactory->shouldReceive('mailer')->andReturn($this->mailer);
        $this->markdown = m::mock(Markdown::class);

        $app->extend(Markdown::class, function () {
            return $this->markdown;
        });

        $app->extend(Mailer::class, function () {
            return $this->mailer;
        });

        $app->extend(MailFactory::class, function () {
            return $this->mailFactory;
        });
    }

    protected function setUp()
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('name')->nullable();
        });
    }

    public function testMailIsSent()
    {
        $notification = new TestMailNotification;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@laravel.com',
        ]);

        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->mailer->shouldReceive('send')->once()->with(
            ['html' => 'htmlContent', 'text' => 'textContent'],
            array_merge($notification->toMail($user)->toArray(), [
                '__laravel_notification_id' => $notification->id,
                '__laravel_notification' => get_class($notification),
                '__laravel_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@laravel.com']);

                $message->shouldReceive('cc')->once()->with('cc@deepblue.com', 'cc');

                $message->shouldReceive('bcc')->once()->with('bcc@deepblue.com', 'bcc');

                $message->shouldReceive('from')->once()->with('jack@deepblue.com', 'Jacques Mayol');

                $message->shouldReceive('replyTo')->once()->with('jack@deepblue.com', 'Jacques Mayol');

                $message->shouldReceive('subject')->once()->with('Test Mail Notification');

                $message->shouldReceive('setPriority')->once()->with(1);

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentToNamedAddress()
    {
        $notification = new TestMailNotification;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUserWithNamedAddress::forceCreate([
            'email' => 'taylor@laravel.com',
            'name' => 'Taylor Otwell',
        ]);

        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->mailer->shouldReceive('send')->once()->with(
            ['html' => 'htmlContent', 'text' => 'textContent'],
            array_merge($notification->toMail($user)->toArray(), [
                '__laravel_notification_id' => $notification->id,
                '__laravel_notification' => get_class($notification),
                '__laravel_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@laravel.com' => 'Taylor Otwell', 'foo_taylor@laravel.com']);

                $message->shouldReceive('cc')->once()->with('cc@deepblue.com', 'cc');

                $message->shouldReceive('bcc')->once()->with('bcc@deepblue.com', 'bcc');

                $message->shouldReceive('from')->once()->with('jack@deepblue.com', 'Jacques Mayol');

                $message->shouldReceive('replyTo')->once()->with('jack@deepblue.com', 'Jacques Mayol');

                $message->shouldReceive('subject')->once()->with('Test Mail Notification');

                $message->shouldReceive('setPriority')->once()->with(1);

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentWithSubject()
    {
        $notification = new TestMailNotificationWithSubject;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@laravel.com',
        ]);

        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->mailer->shouldReceive('send')->once()->with(
            ['html' => 'htmlContent', 'text' => 'textContent'],
            array_merge($notification->toMail($user)->toArray(), [
                '__laravel_notification_id' => $notification->id,
                '__laravel_notification' => get_class($notification),
                '__laravel_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['taylor@laravel.com']);

                $message->shouldReceive('subject')->once()->with('mail custom subject');

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentToMultipleAdresses()
    {
        $notification = new TestMailNotificationWithSubject;
        $notification->id = Str::uuid()->toString();

        $user = NotifiableUserWithMultipleAddreses::forceCreate([
            'email' => 'taylor@laravel.com',
        ]);

        $this->markdown->shouldReceive('render')->once()->andReturn('htmlContent');
        $this->markdown->shouldReceive('renderText')->once()->andReturn('textContent');

        $this->mailer->shouldReceive('send')->once()->with(
            ['html' => 'htmlContent', 'text' => 'textContent'],
            array_merge($notification->toMail($user)->toArray(), [
                '__laravel_notification_id' => $notification->id,
                '__laravel_notification' => get_class($notification),
                '__laravel_notification_queued' => false,
            ]),
            m::on(function ($closure) {
                $message = m::mock(Message::class);

                $message->shouldReceive('to')->once()->with(['foo_taylor@laravel.com', 'bar_taylor@laravel.com']);

                $message->shouldReceive('subject')->once()->with('mail custom subject');

                $closure($message);

                return true;
            })
        );

        $user->notify($notification);
    }

    public function testMailIsSentUsingMailable()
    {
        $notification = new TestMailNotificationWithMailable;

        $user = NotifiableUser::forceCreate([
            'email' => 'taylor@laravel.com',
        ]);

        $user->notify($notification);
    }
}

class NotifiableUser extends Model
{
    use Notifiable;

    public $table = 'users';
    public $timestamps = false;
}

class NotifiableUserWithNamedAddress extends NotifiableUser
{
    public function routeNotificationForMail($notification)
    {
        return [
            $this->email => $this->name,
            'foo_'.$this->email,
        ];
    }
}

class NotifiableUserWithMultipleAddreses extends NotifiableUser
{
    public function routeNotificationForMail($notification)
    {
        return [
            'foo_'.$this->email,
            'bar_'.$this->email,
        ];
    }
}

class TestMailNotification extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->priority(1)
            ->cc('cc@deepblue.com', 'cc')
            ->bcc('bcc@deepblue.com', 'bcc')
            ->from('jack@deepblue.com', 'Jacques Mayol')
            ->replyTo('jack@deepblue.com', 'Jacques Mayol')
            ->line('The introduction to the notification.')
            ->mailer('foo');
    }
}

class TestMailNotificationWithSubject extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('mail custom subject')
            ->line('The introduction to the notification.');
    }
}

class TestMailNotificationWithMailable extends Notification
{
    public function via($notifiable)
    {
        return [MailChannel::class];
    }

    public function toMail($notifiable)
    {
        $mailable = m::mock(Mailable::class);

        $mailable->shouldReceive('send')->once();

        return $mailable;
    }
}
