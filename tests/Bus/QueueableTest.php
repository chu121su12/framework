<?php

namespace Illuminate\Tests\Bus;

use Illuminate\Bus\Queueable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (PHP_VERSION_ID >= 80100) {
    include_once 'Enums.php';
}

class QueueableTest extends TestCase
{
    public static function connectionDataProvider()/*: array*/
    {
        if (\PHP_VERSION_ID < 80100) {
            return [
                'uses string' => ['redis', 'redis'],
                'uses null' => [null, null],
            ];
        }

        return [
            'uses string' => ['redis', 'redis'],
            'uses BackedEnum #1' => [ConnectionEnum::SQS, 'sqs'],
            'uses BackedEnum #2' => [ConnectionEnum::REDIS, 'redis'],
            'uses null' => [null, null],
        ];
    }

    /** @dataProvider connectionDataProvider */
    #[DataProvider('connectionDataProvider')]
    public function testOnConnection(/*mixed */$connection, /*?string */$expected)/*: void*/
    {
        $connection = backport_type_check('mixed', $connection);

        $expected = backport_type_check('?string ', $expected);

        $job = new FakeJob();
        $job->onConnection($connection);

        $this->assertSame($job->connection, $expected);
    }

    /** @dataProvider connectionDataProvider */
    #[DataProvider('connectionDataProvider')]
    public function testAllOnConnection(/*mixed */$connection, /*?string */$expected)/*: void*/
    {
        $connection = backport_type_check('mixed', $connection);

        $expected = backport_type_check('?string ', $expected);

        $job = new FakeJob();
        $job->allOnConnection($connection);

        $this->assertSame($job->connection, $expected);
        $this->assertSame($job->chainConnection, $expected);
    }

    public static function queuesDataProvider()/*: array*/
    {
        if (\PHP_VERSION_ID < 80100) {
            return [
                'uses string' => ['high', 'high'],
                'uses null' => [null, null],
                ];
        }

        return [
            'uses string' => ['high', 'high'],
            'uses BackedEnum #1' => [QueueEnum::DEFAULT_, 'default'],
            'uses BackedEnum #2' => [QueueEnum::HIGH, 'high'],
            'uses null' => [null, null],
        ];
    }

    /** @dataProvider queuesDataProvider */
    #[DataProvider('queuesDataProvider')]
    public function testOnQueue(/*mixed */$queue, /*?string */$expected)/*: void*/
    {
        $queue = backport_type_check('mixed', $queue);

        $expected = backport_type_check('?string ', $expected);

        $job = new FakeJob();
        $job->onQueue($queue);

        $this->assertSame($job->queue, $expected);
    }

    /** @dataProvider queuesDataProvider */
    #[DataProvider('queuesDataProvider')]
    public function testAllOnQueue(/*mixed */$queue, /*?string */$expected)/*: void*/
    {
        $queue = backport_type_check('mixed', $queue);

        $expected = backport_type_check('?string ', $expected);

        $job = new FakeJob();
        $job->allOnQueue($queue);

        $this->assertSame($job->queue, $expected);
        $this->assertSame($job->chainQueue, $expected);
    }
}

class FakeJob
{
    use Queueable;
}
