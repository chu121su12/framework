<?php

namespace Illuminate\Tests\Integration\Mail;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;

class MailRoundRobinTransportTest extends TestCase
{
    #[WithConfig('mail.default', 'roundrobin')]
    #[WithConfig('mail.mailers.roundrobin', ['transport' => 'roundrobin', 'mailers' => ['sendmail', 'array']])]
    public function testGetRoundRobinTransportWithConfiguredTransports()
    {
        $config = Arr::dot(Config::get('mail'));
        Config::set([
            'mail.default' => 'roundrobin',
            'mail.mailers.roundrobin.transport' => 'roundrobin',
            'mail.mailers.roundrobin.mailers' => ['sendmail', 'array'],
        ]);

        $transport = app('mailer')->getSymfonyTransport();
        $this->assertInstanceOf(RoundRobinTransport::class, $transport);

        Config::set($config);
    }

    #[WithConfig('mail.driver', 'roundrobin')]
    #[WithConfig('mail.mailers', ['sendmail', 'array'])]
    #[WithConfig('mail.sendmail', '/usr/sbin/sendmail -bs')]
    public function testGetRoundRobinTransportWithLaravel6StyleMailConfiguration()
    {
        $config = Arr::dot(Config::get('mail'));
        Config::set([
            'mail.driver' => 'roundrobin',
            'mail.mailers' => ['sendmail', 'array'],
            'mail.sendmail' => '/usr/sbin/sendmail -bs',
        ]);

        $transport = app('mailer')->getSymfonyTransport();
        $this->assertInstanceOf(RoundRobinTransport::class, $transport);

        Config::set($config);
    }
}
