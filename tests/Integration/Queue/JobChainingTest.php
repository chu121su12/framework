<?php

namespace Illuminate\Tests\Integration\Queue;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase;

#[WithMigration('queue')]
class JobChainingTest extends TestCase
{
    use DatabaseMigrations;

    public static $catchCallbackRan = false;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.connections.sync1', [
            'driver' => 'sync',
        ]);

        $app['config']->set('queue.connections.sync2', [
            'driver' => 'sync',
        ]);
    }

    protected function setUp()/*: void*/
    {
        parent::setUp();

        JobRunRecorder::reset();
    }

    protected function tearDown()/*: void*/
    {
        JobChainingTestFirstJob::$ran = false;
        JobChainingTestSecondJob::$ran = false;
        JobChainingTestThirdJob::$ran = false;
        static::$catchCallbackRan = false;
    }

    public function testJobsCanBeChainedOnSuccess()
    {
        JobChainingTestFirstJob::dispatch()->chain([
            new JobChainingTestSecondJob,
        ]);

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsCanBeChainedOnSuccessUsingPendingChain()
    {
        JobChainingTestFirstJob::withChain([
            new JobChainingTestSecondJob,
        ])->dispatch();

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsCanBeChainedOnSuccessUsingBusFacade()
    {
        Bus::dispatchChain([
            new JobChainingTestFirstJob,
            new JobChainingTestSecondJob,
        ]);

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsCanBeChainedOnSuccessUsingBusFacadeAsArguments()
    {
        Bus::dispatchChain(
            new JobChainingTestFirstJob,
            new JobChainingTestSecondJob
        );

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsChainedOnExplicitDelete()
    {
        JobChainingTestDeletingJob::dispatch()->chain([
            new JobChainingTestSecondJob,
        ]);

        $this->assertTrue(JobChainingTestDeletingJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsCanBeChainedOnSuccessWithSeveralJobs()
    {
        JobChainingTestFirstJob::dispatch()->chain([
            new JobChainingTestSecondJob,
            new JobChainingTestThirdJob,
        ]);

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
        $this->assertTrue(JobChainingTestThirdJob::$ran);
    }

    public function testJobsCanBeChainedOnSuccessUsingHelper()
    {
        dispatch(new JobChainingTestFirstJob)->chain([
            new JobChainingTestSecondJob,
        ]);

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testJobsCanBeChainedViaQueue()
    {
        Queue::connection('sync')->push((new JobChainingTestFirstJob)->chain([
            new JobChainingTestSecondJob,
        ]));

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(JobChainingTestSecondJob::$ran);
    }

    public function testSecondJobIsNotFiredIfFirstFailed()
    {
        Queue::connection('sync')->push((new JobChainingTestFailingJob)->chain([
            new JobChainingTestSecondJob,
        ]));

        $this->assertFalse(JobChainingTestSecondJob::$ran);
    }

    public function testSecondJobIsNotFiredIfFirstReleased()
    {
        Queue::connection('sync')->push((new JobChainingTestReleasingJob)->chain([
            new JobChainingTestSecondJob,
        ]));

        $this->assertFalse(JobChainingTestSecondJob::$ran);
    }

    public function testThirdJobIsNotFiredIfSecondFails()
    {
        Queue::connection('sync')->push((new JobChainingTestFirstJob)->chain([
            new JobChainingTestFailingJob,
            new JobChainingTestThirdJob,
        ]));

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertFalse(JobChainingTestThirdJob::$ran);
    }

    public function testCatchCallbackIsCalledOnFailure()
    {
        Bus::chain([
            new JobChainingTestFirstJob,
            new JobChainingTestFailingJob,
            new JobChainingTestSecondJob,
        ])->catch_(static function () {
            self::$catchCallbackRan = true;
        })->dispatch();

        $this->assertTrue(JobChainingTestFirstJob::$ran);
        $this->assertTrue(static::$catchCallbackRan);
        $this->assertFalse(JobChainingTestSecondJob::$ran);
    }

    public function testChainJobsUseSameConfig()
    {
        JobChainingTestFirstJob::dispatch()->allOnQueue('some_queue')->allOnConnection('sync1')->chain([
            new JobChainingTestSecondJob,
            new JobChainingTestThirdJob,
        ]);

        $this->assertSame('some_queue', JobChainingTestFirstJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestFirstJob::$usedConnection);

        $this->assertSame('some_queue', JobChainingTestSecondJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestSecondJob::$usedConnection);

        $this->assertSame('some_queue', JobChainingTestThirdJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestThirdJob::$usedConnection);
    }

    public function testChainJobsUseOwnConfig()
    {
        JobChainingTestFirstJob::dispatch()->allOnQueue('some_queue')->allOnConnection('sync1')->chain([
            (new JobChainingTestSecondJob)->onQueue('another_queue')->onConnection('sync2'),
            new JobChainingTestThirdJob,
        ]);

        $this->assertSame('some_queue', JobChainingTestFirstJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestFirstJob::$usedConnection);

        $this->assertSame('another_queue', JobChainingTestSecondJob::$usedQueue);
        $this->assertSame('sync2', JobChainingTestSecondJob::$usedConnection);

        $this->assertSame('some_queue', JobChainingTestThirdJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestThirdJob::$usedConnection);
    }

    public function testChainJobsUseDefaultConfig()
    {
        JobChainingTestFirstJob::dispatch()->onQueue('some_queue')->onConnection('sync1')->chain([
            (new JobChainingTestSecondJob)->onQueue('another_queue')->onConnection('sync2'),
            new JobChainingTestThirdJob,
        ]);

        $this->assertSame('some_queue', JobChainingTestFirstJob::$usedQueue);
        $this->assertSame('sync1', JobChainingTestFirstJob::$usedConnection);

        $this->assertSame('another_queue', JobChainingTestSecondJob::$usedQueue);
        $this->assertSame('sync2', JobChainingTestSecondJob::$usedConnection);

        $this->assertNull(JobChainingTestThirdJob::$usedQueue);
        $this->assertNull(JobChainingTestThirdJob::$usedConnection);
    }

    /**
     * @requires PHP 7
     */
    public function testChainJobsCanBePrepended()
    {
        JobChainAddingPrependingJob::withChain([new JobChainAddingExistingJob])->dispatch();

        $this->assertNotNull(JobChainAddingAddedJob::$ranAt);
        $this->assertNotNull(JobChainAddingExistingJob::$ranAt);
        $this->assertTrue(JobChainAddingAddedJob::$ranAt->isBefore(JobChainAddingExistingJob::$ranAt));
    }

    public function testChainJobsCanBePrependedWithoutExistingChain()
    {
        JobChainAddingPrependingJob::dispatch();

        $this->assertNotNull(JobChainAddingAddedJob::$ranAt);
    }

    /**
     * @requires PHP 7
     */
    public function testChainJobsCanBeAppended()
    {
        JobChainAddingAppendingJob::withChain([new JobChainAddingExistingJob])->dispatch();

        $this->assertNotNull(JobChainAddingAddedJob::$ranAt);
        $this->assertNotNull(JobChainAddingExistingJob::$ranAt);
        $this->assertTrue(JobChainAddingAddedJob::$ranAt->isAfter(JobChainAddingExistingJob::$ranAt));
    }

    public function testChainJobsCanBeAppendedWithoutExistingChain()
    {
        JobChainAddingAppendingJob::dispatch();

        $this->assertNotNull(JobChainAddingAddedJob::$ranAt);
    }

    public function testBatchCanBeAddedToChain()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestBatchedJob('b2'),
                new JobChainingTestBatchedJob('b3'),
                new JobChainingTestBatchedJob('b4'),
            ]),
            new JobChainingNamedTestJob('c3'),
        ])->dispatch();

        $this->assertEquals(['c1', 'c2', 'b1', 'b2', 'b3', 'b4', 'c3'], JobRunRecorder::$results);
    }

    public function testDynamicBatchCanBeAddedToChain()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestBatchedJob('b2', /*times: */4),
                new JobChainingTestBatchedJob('b3'),
                new JobChainingTestBatchedJob('b4'),
            ]),
            new JobChainingNamedTestJob('c3'),
        ])->dispatch();

        $this->assertEquals(['c1', 'c2', 'b1', 'b2-0', 'b2-1', 'b2-2', 'b2-3', 'b2', 'b3', 'b4', 'c3'], JobRunRecorder::$results);
    }

    public function testChainBatchChain()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                [
                    new JobChainingNamedTestJob('bc1'),
                    new JobChainingNamedTestJob('bc2'),
                ],
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestBatchedJob('b2', /*times: */4),
                new JobChainingTestBatchedJob('b3'),
                new JobChainingTestBatchedJob('b4'),
            ]),
            new JobChainingNamedTestJob('c3'),
        ])->dispatch();

        $this->assertEquals(['c1', 'c2', 'bc1', 'bc2', 'b1', 'b2-0', 'b2-1', 'b2-2', 'b2-3', 'b2', 'b3', 'b4', 'c3'], JobRunRecorder::$results);
    }

    public function testChainBatchChainBatch()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                [
                    new JobChainingNamedTestJob('bc1'),
                    new JobChainingNamedTestJob('bc2'),
                    Bus::batch([
                        new JobChainingTestBatchedJob('bb1'),
                        new JobChainingTestBatchedJob('bb2'),
                    ]),
                ],
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestBatchedJob('b2', /*times: */4),
                new JobChainingTestBatchedJob('b3'),
                new JobChainingTestBatchedJob('b4'),
            ]),
            new JobChainingNamedTestJob('c3'),
        ])->dispatch();

        $this->assertEquals(['c1', 'c2', 'bc1', 'bc2', 'bb1', 'bb2', 'b1', 'b2-0', 'b2-1', 'b2-2', 'b2-3', 'b2', 'b3', 'b4', 'c3'], JobRunRecorder::$results);
    }

    public function testBatchCatchCallbacks()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                new JobChainingTestFailingBatchedJob('fb1'),
            ])->catch_(function () { return JobRunRecorder::recordFailure('batch failed'); }),
            new JobChainingNamedTestJob('c3'),
        ])->catch_(function () { return JobRunRecorder::recordFailure('chain failed'); })->dispatch();

        $this->assertEquals(['c1', 'c2'], JobRunRecorder::$results);
        $this->assertEquals(['batch failed', 'chain failed'], JobRunRecorder::$failures);
    }

    public function testChainBatchFailureAllowed()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestFailingBatchedJob('b2'),
                new JobChainingTestBatchedJob('b3'),
            ])->allowFailures()->catch_(function () { return JobRunRecorder::recordFailure('batch failed'); }),
            new JobChainingNamedTestJob('c3'),
        ])->catch_(function () { return JobRunRecorder::recordFailure('chain failed'); })->dispatch();

        $this->assertEquals(['c1', 'c2', 'b1', 'b3', 'c3'], JobRunRecorder::$results);
        // Only the batch failed, but the chain should keep going since the batch allows failures
        $this->assertEquals(['batch failed'], JobRunRecorder::$failures);
    }

    public function testChainBatchFailureNotAllowed()
    {
        Bus::chain([
            new JobChainingNamedTestJob('c1'),
            new JobChainingNamedTestJob('c2'),
            Bus::batch([
                new JobChainingTestBatchedJob('b1'),
                new JobChainingTestFailingBatchedJob('b2'),
                new JobChainingTestBatchedJob('b3'),
            ])->allowFailures(false)->catch_(function () { return JobRunRecorder::recordFailure('batch failed'); }),
            new JobChainingNamedTestJob('c3'),
        ])->catch_(function () { return JobRunRecorder::recordFailure('chain failed'); })->dispatch();

        $this->assertEquals(['c1', 'c2', 'b1', 'b3'], JobRunRecorder::$results);
        $this->assertEquals(['batch failed', 'chain failed'], JobRunRecorder::$failures);
    }
}

class JobChainingTestFirstJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public static $ran = false;

    public static $usedQueue = null;

    public static $usedConnection = null;

    public function handle()
    {
        static::$ran = true;
        static::$usedQueue = $this->queue;
        static::$usedConnection = $this->connection;
    }
}

class JobChainingTestSecondJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public static $ran = false;

    public static $usedQueue = null;

    public static $usedConnection = null;

    public function handle()
    {
        static::$ran = true;
        static::$usedQueue = $this->queue;
        static::$usedConnection = $this->connection;
    }
}

class JobChainingTestThirdJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public static $ran = false;

    public static $usedQueue = null;

    public static $usedConnection = null;

    public function handle()
    {
        static::$ran = true;
        static::$usedQueue = $this->queue;
        static::$usedConnection = $this->connection;
    }
}

class JobChainingTestDeletingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public static $ran = false;

    public function handle()
    {
        static::$ran = true;
        $this->delete();
    }
}

class JobChainingTestReleasingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->release(30);
    }
}

class JobChainingTestFailingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->fail();
    }
}

class JobChainAddingPrependingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->prependToChain(new JobChainAddingAddedJob);
    }
}

class JobChainAddingAppendingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->appendToChain(new JobChainAddingAddedJob);
    }
}

class JobChainAddingExistingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** @var Carbon|null */
    public static $ranAt = null;

    public function handle()
    {
        static::$ranAt = now();
    }
}

class JobChainAddingAddedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** @var Carbon|null */
    public static $ranAt = null;

    public function handle()
    {
        static::$ranAt = now();
    }
}

class JobChainingTestThrowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        throw new \Exception();
    }
}

class JobChainingNamedTestJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public static $results = [];

    public /*string */$id;

    public function __construct(/*string */$id)
    {
        $id = backport_type_check('string', $id);

        $this->id = $id;
    }

    public function handle()
    {
        JobRunRecorder::record($this->id);
    }
}

class JobChainingTestBatchedJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public /*string */$id;

    public /*int */$times;

    public function __construct(/*string */$id, /*int */$times = 0)
    {
        $id = backport_type_check('string', $id);
        $times = backport_type_check('int', $times);

        $this->id = $id;
        $this->times = $times;
    }

    public function handle()
    {
        for ($i = 0; $i < $this->times; $i++) {
            $this->batch()->add(new JobChainingTestBatchedJob($this->id.'-'.$i));
        }
        JobRunRecorder::record($this->id);
    }
}

class JobChainingTestFailingBatchedJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->fail();
    }
}

class JobRunRecorder
{
    public static $results = [];

    public static $failures = [];

    public static function record(/*string */$id)
    {
        $id = backport_type_check('string', $id);

        self::$results[] = $id;
    }

    public static function recordFailure(/*string */$message)
    {
        $message = backport_type_check('string', $message);

        self::$failures[] = $message;

        return $message;
    }

    public static function reset()
    {
        self::$results = [];
        self::$failures = [];
    }
}
