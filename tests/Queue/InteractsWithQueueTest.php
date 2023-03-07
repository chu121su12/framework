<?php

namespace Illuminate\Tests\Queue;

use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\InteractsWithQueue;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class InteractsWithQueueTest_testCreatesAnExceptionFromString_class
        {
            use InteractsWithQueue;

            /*public $job;*/
        }

class InteractsWithQueueTest extends TestCase
{
    public function testCreatesAnExceptionFromString()
    {
        $queueJob = m::mock(Job::class);
        $queueJob->shouldReceive('fail')->withArgs(function ($e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Whoops!', $e->getMessage());

            return true;
        });

        $job = new InteractsWithQueueTest_testCreatesAnExceptionFromString_class;

        $job->job = $queueJob;
        $job->fail('Whoops!');
    }
}
