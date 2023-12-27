<?php

namespace Illuminate\Tests\Integration\Mail;

use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;

class MailRoundRobinTransportTest extends TestCase
{
    #[WithConfig('mail.default', 'roundrobin')]
    #[WithConfig('mail.mailers.roundrobin', ['transport' => 'roundrobin', 'mailers' => ['sendmail', 'array']])]
    public function testGetRoundRobinTransportWithConfiguredTransports()
    {
        if (! class_exists(RoundRobinTransport::class)) {
            $this->markTestSkipped('RoundRobinTransport unavailable');
        }

        $transport = app('mailer')->getSymfonyTransport();
        $this->assertInstanceOf(RoundRobinTransport::class, $transport);
    }

    #[WithConfig('mail.driver', 'roundrobin')]
    #[WithConfig('mail.mailers', ['sendmail', 'array'])]
    #[WithConfig('mail.sendmail', '/usr/sbin/sendmail -bs')]
    public function testGetRoundRobinTransportWithLaravel6StyleMailConfiguration()
    {
        if (! class_exists(RoundRobinTransport::class)) {
            $this->markTestSkipped('RoundRobinTransport unavailable');
        }

        $transport = app('mailer')->getSymfonyTransport();
        $this->assertInstanceOf(RoundRobinTransport::class, $transport);
    }
}
