<?php

namespace Illuminate\Tests\Queue;

use Illuminate\Contracts\Redis\Factory;
use Illuminate\Queue\LuaScripts;
use Illuminate\Queue\Queue;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class QueueRedisQueueTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testPushProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval_')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0]));

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);

        Str::createUuidsNormally();
    }

    public function testPushProperlyPushesJobOntoRedisWithCustomPayloadHook()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval_')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'custom' => 'taylor', 'id' => 'foo', 'attempts' => 0]));

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['custom' => 'taylor'];
        });

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);

        Queue::createPayloadUsing(null);

        Str::createUuidsNormally();
    }

    public function testPushProperlyPushesJobOntoRedisWithTwoCustomPayloadHook()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('eval_')->once()->with(LuaScripts::push(), 2, 'queues:default', 'queues:default:notify', json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'custom' => 'taylor', 'bar' => 'foo', 'id' => 'foo', 'attempts' => 0]));

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['custom' => 'taylor'];
        });

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['bar' => 'foo'];
        });

        $id = $queue->push('foo', ['data']);
        $this->assertSame('foo', $id);

        Queue::createPayloadUsing(null);

        Str::createUuidsNormally();
    }

    public function testDelayedPushProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['availableAt', 'getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->expects($this->once())->method('availableAt')->with(1)->willReturn(2);

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0])
        );

        $id = $queue->later(1, 'foo', ['data']);
        $this->assertSame('foo', $id);

        Str::createUuidsNormally();
    }

    public function testDelayedPushWithDateTimeProperlyPushesJobOntoRedis()
    {
        $uuid = Str::uuid();

        Str::createUuidsUsing(function () use ($uuid) {
            return $uuid;
        });

        $date = Carbon::now();
        $queue = $this->getMockBuilder(RedisQueue::class)->onlyMethods(['availableAt', 'getRandomId'])->setConstructorArgs([$redis = m::mock(Factory::class), 'default'])->getMock();
        $queue->expects($this->once())->method('getRandomId')->willReturn('foo');
        $queue->expects($this->once())->method('availableAt')->with($date)->willReturn(2);

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['uuid' => $uuid, 'displayName' => 'foo', 'job' => 'foo', 'maxTries' => null, 'maxExceptions' => null, 'backoff' => null, 'timeout' => null, 'data' => ['data'], 'id' => 'foo', 'attempts' => 0])
        );

        $queue->later($date, 'foo', ['data']);

        Str::createUuidsNormally();
    }
}
