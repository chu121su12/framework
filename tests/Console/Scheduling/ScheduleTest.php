<?php

/*declare(strict_types=1);*/

namespace Illuminate\Tests\Console\Scheduling;

use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\SchedulingMutex;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Tests\Console\Fixtures\JobToTestWithSchedule;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScheduleTest_jobHonoursDisplayNameIfMethodExistsProvider_class implements ShouldQueue
        {
            public function displayName()/*: string*/
            {
                return 'testJob-123';
            }
        }

#[CoversClass(Schedule::class)]
final class ScheduleTest extends TestCase
{
    private /*Container */$container;
    private /*EventMutex&MockInterface */$eventMutex;
    private /*SchedulingMutex&MockInterface */$schedulingMutex;

    protected function setUp()/*: void*/
    {
        parent::setUp();

        $this->container = new Container;
        Container::setInstance($this->container);
        $this->eventMutex = m::mock(EventMutex::class);
        $this->container->instance(EventMutex::class, $this->eventMutex);
        $this->schedulingMutex = m::mock(SchedulingMutex::class);
        $this->container->instance(SchedulingMutex::class, $this->schedulingMutex);
    }

    /**
     * @dataProvider jobHonoursDisplayNameIfMethodExistsProvider
     */
    #[DataProvider('jobHonoursDisplayNameIfMethodExistsProvider')]
    public function testJobHonoursDisplayNameIfMethodExists(/*string|object */$job, /*string */$jobName)/*: void*/
    {
        $job = backport_type_check('string|object', $job);

        $jobName = backport_type_check('string', $jobName);

        $schedule = new Schedule();
        $scheduledJob = $schedule->job($job);
        self::assertSame($jobName, $scheduledJob->description);
    }

    public static function jobHonoursDisplayNameIfMethodExistsProvider()/*: array*/
    {
        $job = new ScheduleTest_jobHonoursDisplayNameIfMethodExistsProvider_class;

        return [
            [JobToTestWithSchedule::class, JobToTestWithSchedule::class],
            [new JobToTestWithSchedule, JobToTestWithSchedule::class],
            [$job, 'testJob-123'],
        ];
    }
}
